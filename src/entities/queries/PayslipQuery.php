<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\entities\queries;

use yii\db\ActiveQuery;

/**
 * ActiveQuery для сущности Payslip.
 */
class PayslipQuery extends ActiveQuery
{
    /**
     * Фильтрует по году.
     */
    public function byYear(int $year): static
    {
        return $this->andWhere(['period_year' => $year]);
    }

    /**
     * Фильтрует по диапазону годов (включительно).
     */
    public function byYearRange(int $from, int $to): static
    {
        return $this->andWhere(['between', 'period_year', $from, $to]);
    }

    /**
     * Сортировка по периоду (сначала новые).
     */
    public function latestFirst(): static
    {
        return $this->orderBy(['period_year' => SORT_DESC, 'period_month' => SORT_DESC]);
    }

    /**
     * Сортировка по периоду (сначала старые).
     */
    public function oldestFirst(): static
    {
        return $this->orderBy(['period_year' => SORT_ASC, 'period_month' => SORT_ASC]);
    }
}
