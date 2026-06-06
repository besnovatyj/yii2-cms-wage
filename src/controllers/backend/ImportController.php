<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\controllers\backend;

use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\entities\PayslipItem;
use DateTimeImmutable;
use RuntimeException;
use Throwable;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Одноразовый контроллер импорта данных из старой таблицы `wage_months`.
 *
 * Порядок использования:
 *   1. Загрузите полный дамп старой БД в текущую базу данных.
 *   2. Откройте страницу (GET) — увидите превью импортируемых данных.
 *   3. Нажмите «Запустить импорт» (POST) — данные перенесутся в новые таблицы.
 *
 * Логика преобразования:
 *   - `date` (UNIX timestamp) → `period_year` + `period_month`
 *   - `items` (JSON array или object) → строки `wage_payslip_items`
 *   - Элементы с отрицательной суммой помечаются как `is_tax = 1`, сумма сохраняется как положительная
 *   - Уже существующие квитки (по году/месяцу) пропускаются
 *
 * ВНИМАНИЕ: Удалите этот контроллер после успешного импорта.
 */
class ImportController extends Controller
{
    /**
     * Имя старой таблицы (без префикса Yii2, т.к. загружается внешним дампом).
     */
    private const string OLD_TABLE = 'wage_months';

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'run' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Страница превью: показывает все записи из `wage_months` и статус каждой.
     */
    public function actionIndex(): string
    {
        $rows    = $this->fetchOldRows();
        $preview = [];

        foreach ($rows as $row) {
            $parsed = $this->parseRow($row);
            $exists = $parsed['error'] === null && Payslip::find()
                ->andWhere(['period_year' => $parsed['year'], 'period_month' => $parsed['month']])
                ->exists();

            $preview[] = [
                'old_id' => (int)$row['id'],
                'year'   => $parsed['year'],
                'month'  => $parsed['month'],
                'items'  => $parsed['items'],
                'exists' => $exists,
                'error'  => $parsed['error'],
            ];
        }

        return $this->render('index', [
            'preview'      => $preview,
            'tableExists'  => $this->oldTableExists(),
        ]);
    }

    /**
     * Запускает импорт всех записей из `wage_months` в новую структуру.
     * Уже существующие периоды пропускаются.
     */
    public function actionRun(): Response
    {
        $rows        = $this->fetchOldRows();
        $imported    = 0;
        $skippedRows = [];
        $parseErrors = [];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($rows as $row) {
                $parsed = $this->parseRow($row);

                if ($parsed['error'] !== null) {
                    $parseErrors[] = "old_id={$row['id']}: {$parsed['error']}";
                    continue;
                }

                $alreadyExists = Payslip::find()
                    ->andWhere(['period_year' => $parsed['year'], 'period_month' => $parsed['month']])
                    ->exists();

                if ($alreadyExists) {
                    $skippedRows[] = ['year' => $parsed['year'], 'month' => $parsed['month']];
                    continue;
                }

                $payslip = Payslip::create($parsed['year'], $parsed['month'], null);
                if (!$payslip->save()) {
                    throw new RuntimeException(
                        "Ошибка сохранения квитка old_id={$row['id']}: "
                        . json_encode($payslip->getErrors(), JSON_UNESCAPED_UNICODE)
                    );
                }

                foreach ($parsed['items'] as $index => $itemData) {
                    $item = PayslipItem::create(
                        $payslip->id,
                        $itemData['title'],
                        $itemData['amount'],
                        $itemData['is_tax'],
                        $index,
                    );
                    if (!$item->save()) {
                        throw new RuntimeException(
                            "Ошибка сохранения строки квитка (old_id={$row['id']}, индекс={$index}): "
                            . json_encode($item->getErrors(), JSON_UNESCAPED_UNICODE)
                        );
                    }
                }

                $imported++;
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Импорт прерван из-за ошибки: ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        $skipped = count($skippedRows);
        $msg = "Импорт завершён. Импортировано: {$imported}, пропущено (уже существуют): {$skipped}.";

        if ($parseErrors) {
            $msg .= ' Пропущено из-за ошибок парсинга (' . count($parseErrors) . '): '
                . implode('; ', $parseErrors);
        }

        Yii::$app->session->setFlash($parseErrors ? 'warning' : 'success', $msg);

        if ($skippedRows) {
            Yii::$app->session->setFlash('skippedRows', $skippedRows);
        }

        return $this->redirect(['index']);
    }

    /**
     * Читает все строки из старой таблицы, сортируя по дате.
     *
     * @return array<int, array{id: string, date: string, items: string}>
     */
    private function fetchOldRows(): array
    {
        if (!$this->oldTableExists()) {
            return [];
        }

        try {
            return Yii::$app->db
                ->createCommand('SELECT `id`, `date`, `items` FROM `' . self::OLD_TABLE . '` ORDER BY CAST(`date` AS UNSIGNED) ASC')
                ->queryAll();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Проверяет, существует ли старая таблица в текущей БД.
     */
    private function oldTableExists(): bool
    {
        try {
            $result = Yii::$app->db
                ->createCommand("SHOW TABLES LIKE '" . self::OLD_TABLE . "'")
                ->queryScalar();
            return $result !== false && $result !== null;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Разбирает строку из старой таблицы в промежуточный формат.
     *
     * @param array{id: string, date: string, items: string} $row
     * @return array{
     *     year: int,
     *     month: int,
     *     items: list<array{title: string, amount: float, is_tax: bool}>,
     *     error: string|null
     * }
     */
    private function parseRow(array $row): array
    {
        $result = ['year' => 0, 'month' => 0, 'items' => [], 'error' => null];

        // --- Дата ---
        $timestamp = (int)$row['date'];
        if ($timestamp <= 0) {
            $result['error'] = "Некорректный timestamp: «{$row['date']}»";
            return $result;
        }

        $dt = new DateTimeImmutable('@' . $timestamp);
        $result['year']  = (int)$dt->format('Y');
        $result['month'] = (int)$dt->format('n');

        // --- JSON items ---
        // В старой базе поле иногда двойно сериализовано:
        // строка содержит JSON-строку, которая сама является JSON-массивом/объектом.
        $decoded = json_decode($row['items'], true);
        if (is_string($decoded)) {
            // Двойная сериализация — декодируем ещё раз
            $decoded = json_decode($decoded, true);
        }

        if (!is_array($decoded)) {
            $result['error'] = 'Не удалось распарсить JSON items (old_id=' . $row['id'] . ')';
            return $result;
        }

        // Поддержка как массива [{...}], так и объекта {"0":{...},"1":{...}}
        foreach (array_values($decoded) as $index => $item) {
            if (!is_array($item) || !array_key_exists('type', $item) || !array_key_exists('value', $item)) {
                continue;
            }

            $title     = trim((string)$item['type']);
            // Суммы в старой базе хранились в копейках — конвертируем в рубли
            $rawAmount = (float)str_replace([' ', "\u{00A0}"], '', (string)$item['value']) / 100;

            // Отрицательная сумма = налог/вычет; сохраняем как положительную с флагом is_tax
            $isTax  = $rawAmount < 0.0;
            $amount = abs($rawAmount);

            if ($title === '') {
                continue;
            }

            $result['items'][] = [
                'title'  => $title,
                'amount' => $amount,
                'is_tax' => $isTax,
            ];
        }

        return $result;
    }
}
