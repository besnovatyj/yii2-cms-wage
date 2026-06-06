<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\repositories;

use Besnovatyj\Wage\entities\Payslip;
use RuntimeException;
use Throwable;

/**
 * Репозиторий квитков — операции CRUD над сущностью Payslip.
 */
class PayslipRepository
{
    /**
     * Находит квиток по ID.
     *
     * @throws NotFoundException если квиток не найден
     */
    public function get(int $id): Payslip
    {
        if (($payslip = Payslip::findOne($id)) === null) {
            throw new NotFoundException("Квиток с ID={$id} не найден.");
        }

        return $payslip;
    }

    /**
     * Проверяет, существует ли квиток за указанный период.
     */
    public function existsByPeriod(int $year, int $month, ?int $excludeId = null): bool
    {
        $query = Payslip::find()
            ->andWhere(['period_year' => $year, 'period_month' => $month]);

        if ($excludeId !== null) {
            $query->andWhere(['<>', 'id', $excludeId]);
        }

        return $query->exists();
    }

    /**
     * Сохраняет квиток.
     *
     * @throws RuntimeException при ошибке сохранения
     */
    public function save(Payslip $payslip): void
    {
        if (!$payslip->save()) {
            throw new RuntimeException('Ошибка сохранения квитка: ' . json_encode($payslip->getErrors()));
        }
    }

    /**
     * Удаляет квиток.
     *
     * @throws RuntimeException при ошибке удаления
     * @throws Throwable
     */
    public function remove(Payslip $payslip): void
    {
        if (!$payslip->delete()) {
            throw new RuntimeException('Ошибка удаления квитка.');
        }
    }
}
