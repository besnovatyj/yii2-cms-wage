<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\services\backup;

use Besnovatyj\Wage\dto\BackupImportResult;
use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\entities\PayslipItem;
use Besnovatyj\Wage\repositories\PayslipRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Throwable;
use Yii;

/**
 * Сервис резервного копирования и восстановления данных квитков в формате JSON.
 *
 * Формат файла:
 * {
 *   "version": "1.0",
 *   "exported_at": "2026-03-12T10:00:00+03:00",
 *   "payslips_count": 12,
 *   "payslips": [
 *     {
 *       "period_year": 2025,
 *       "period_month": 3,
 *       "note": null,
 *       "created_at": "2025-03-01 10:00:00",
 *       "items": [
 *         { "title": "Оклад", "amount": "50000.00", "is_tax": false, "sort_order": 0 }
 *       ]
 *     }
 *   ]
 * }
 */
class BackupService
{
    private const string FORMAT_VERSION = '1.0';

    public function __construct(
        private readonly PayslipRepository $payslips,
    ) {
    }

    /**
     * Экспортирует все квитки в JSON-строку.
     *
     * @throws RuntimeException при ошибке сериализации
     */
    public function export(): string
    {
        $payslips = Payslip::find()
            ->with('items')
            ->orderBy(['period_year' => SORT_ASC, 'period_month' => SORT_ASC])
            ->all();

        $data = [
            'version'        => self::FORMAT_VERSION,
            'exported_at'    => (new DateTimeImmutable())->format(DateTimeImmutable::ATOM),
            'payslips_count' => count($payslips),
            'payslips'       => array_map([$this, 'serializePayslip'], $payslips),
        ];

        try {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Ошибка сериализации данных: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Импортирует квитки из JSON-строки.
     * Квитки за периоды, которые уже существуют в базе, пропускаются.
     *
     * @throws InvalidArgumentException при невалидном JSON или некорректной структуре файла
     * @throws Throwable при ошибке сохранения в БД
     */
    public function import(string $json): BackupImportResult
    {
        $data = $this->parseAndValidate($json);

        $imported      = 0;
        $skipped       = 0;
        $skippedPeriods = [];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($data['payslips'] as $payslipData) {
                $year  = (int)$payslipData['period_year'];
                $month = (int)$payslipData['period_month'];

                if ($this->payslips->existsByPeriod($year, $month)) {
                    $skipped++;
                    $skippedPeriods[] = "{$month}/{$year}";
                    continue;
                }

                $payslip = Payslip::create(
                    $year,
                    $month,
                    isset($payslipData['note']) && $payslipData['note'] !== null
                        ? (string)$payslipData['note']
                        : null,
                );

                if (!$payslip->save()) {
                    throw new RuntimeException(
                        "Ошибка сохранения квитка {$month}/{$year}: "
                        . json_encode($payslip->getErrors(), JSON_UNESCAPED_UNICODE)
                    );
                }

                foreach ($payslipData['items'] as $index => $itemData) {
                    $item = PayslipItem::create(
                        $payslip->id,
                        (string)$itemData['title'],
                        (float)$itemData['amount'],
                        (bool)$itemData['is_tax'],
                        isset($itemData['sort_order']) ? (int)$itemData['sort_order'] : $index,
                    );

                    if (!$item->save()) {
                        throw new RuntimeException(
                            "Ошибка сохранения строки квитка {$month}/{$year}: "
                            . json_encode($item->getErrors(), JSON_UNESCAPED_UNICODE)
                        );
                    }
                }

                $imported++;
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return new BackupImportResult($imported, $skipped, $skippedPeriods);
    }

    // ===== Приватные методы =====

    /**
     * Сериализует квиток в массив для JSON.
     *
     * @return array{period_year: int, period_month: int, note: string|null, created_at: string, items: array}
     */
    private function serializePayslip(Payslip $payslip): array
    {
        return [
            'period_year'  => $payslip->period_year,
            'period_month' => $payslip->period_month,
            'note'         => $payslip->note,
            'created_at'   => $payslip->created_at,
            'items'        => array_map([$this, 'serializeItem'], $payslip->items),
        ];
    }

    /**
     * Сериализует строку квитка в массив для JSON.
     *
     * @return array{title: string, amount: string, is_tax: bool, sort_order: int}
     */
    private function serializeItem(PayslipItem $item): array
    {
        return [
            'title'      => $item->title,
            'amount'     => number_format((float)$item->amount, 2, '.', ''),
            'is_tax'     => (bool)$item->is_tax,
            'sort_order' => (int)$item->sort_order,
        ];
    }

    /**
     * Парсит JSON и проверяет структуру файла бэкапа.
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException при невалидном JSON или отсутствии обязательных полей
     */
    private function parseAndValidate(string $json): array
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Некорректный JSON: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Ожидается JSON-объект в корне файла.');
        }

        if (!isset($data['version'])) {
            throw new InvalidArgumentException('Отсутствует поле «version» в файле бэкапа.');
        }

        if (!isset($data['payslips']) || !is_array($data['payslips'])) {
            throw new InvalidArgumentException('Отсутствует или некорректное поле «payslips».');
        }

        foreach ($data['payslips'] as $index => $payslipData) {
            $this->validatePayslipData($payslipData, $index);
        }

        return $data;
    }

    /**
     * Валидирует данные одного квитка из бэкапа.
     *
     * @param mixed $data
     * @throws InvalidArgumentException
     */
    private function validatePayslipData(mixed $data, int $index): void
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException("Квиток #{$index}: ожидается объект.");
        }

        foreach (['period_year', 'period_month', 'items'] as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidArgumentException(
                    "Квиток #{$index}: отсутствует обязательное поле «{$field}»."
                );
            }
        }

        if (!is_array($data['items'])) {
            throw new InvalidArgumentException("Квиток #{$index}: поле «items» должно быть массивом.");
        }

        foreach ($data['items'] as $itemIndex => $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException(
                    "Квиток #{$index}, строка #{$itemIndex}: ожидается объект."
                );
            }

            foreach (['title', 'amount', 'is_tax'] as $field) {
                if (!array_key_exists($field, $item)) {
                    throw new InvalidArgumentException(
                        "Квиток #{$index}, строка #{$itemIndex}: отсутствует поле «{$field}»."
                    );
                }
            }
        }
    }
}
