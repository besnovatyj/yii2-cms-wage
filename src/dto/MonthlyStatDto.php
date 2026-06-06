<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\dto;

/**
 * DTO помесячной статистики для графика и таблиц.
 */
final readonly class MonthlyStatDto
{
    public function __construct(
        public int   $year,
        public int   $month,
        public float $gross,
        public float $tax,
        public float $net,
    ) {
    }

    /**
     * Возвращает метку периода вида «03.2025».
     */
    public function getPeriodLabel(): string
    {
        return sprintf('%02d.%04d', $this->month, $this->year);
    }

    /**
     * Возвращает метку для отображения на оси X (краткое имя месяца + год).
     */
    public function getChartLabel(): string
    {
        $months = [
            1  => 'Янв',
            2  => 'Фев',
            3  => 'Мар',
            4  => 'Апр',
            5  => 'Май',
            6  => 'Июн',
            7  => 'Июл',
            8  => 'Авг',
            9  => 'Сен',
            10 => 'Окт',
            11 => 'Ноя',
            12 => 'Дек',
        ];

        return ($months[$this->month] ?? '?') . "\n" . $this->year;
    }
}
