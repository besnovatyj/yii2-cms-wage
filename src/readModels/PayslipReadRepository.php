<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\readModels;

use Besnovatyj\Wage\dto\MonthlyStatDto;
use Besnovatyj\Wage\dto\YearlyStatDto;
use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\entities\PayslipItem;
use Yii;
use yii\db\Expression;

/**
 * Read-модель для получения агрегированных данных квитков.
 * Используется для построения графиков и сводных таблиц.
 * Не изменяет данные — только читает.
 */
class PayslipReadRepository
{
    /**
     * Возвращает помесячную статистику за диапазон годов.
     *
     * @return MonthlyStatDto[]
     */
    public function findMonthlyStats(int $yearFrom, int $yearTo): array
    {
        $rows = Yii::$app->db->createCommand('
            SELECT
                p.period_year,
                p.period_month,
                COALESCE(SUM(CASE WHEN pi.is_tax = 0 THEN pi.amount ELSE 0 END), 0) AS gross,
                COALESCE(SUM(CASE WHEN pi.is_tax = 1 THEN pi.amount ELSE 0 END), 0) AS tax,
                COALESCE(SUM(CASE WHEN pi.is_tax = 0 THEN pi.amount ELSE 0 END), 0)
                    - COALESCE(SUM(CASE WHEN pi.is_tax = 1 THEN pi.amount ELSE 0 END), 0) AS net
            FROM {{%wage_payslips}} p
            LEFT JOIN {{%wage_payslip_items}} pi ON pi.payslip_id = p.id
            WHERE p.period_year BETWEEN :yearFrom AND :yearTo
            GROUP BY p.period_year, p.period_month
            ORDER BY p.period_year ASC, p.period_month ASC
        ', [':yearFrom' => $yearFrom, ':yearTo' => $yearTo])
            ->queryAll();

        return array_map(
            static fn(array $row) => new MonthlyStatDto(
                year:  (int)$row['period_year'],
                month: (int)$row['period_month'],
                gross: (float)$row['gross'],
                tax:   (float)$row['tax'],
                net:   (float)$row['net'],
            ),
            $rows
        );
    }

    /**
     * Возвращает годовую статистику по всем квиткам.
     *
     * @return YearlyStatDto[]
     */
    public function findYearlyStats(): array
    {
        $rows = Yii::$app->db->createCommand('
            SELECT
                p.period_year,
                COALESCE(SUM(CASE WHEN pi.is_tax = 0 THEN pi.amount ELSE 0 END), 0) AS gross,
                COALESCE(SUM(CASE WHEN pi.is_tax = 1 THEN pi.amount ELSE 0 END), 0) AS tax,
                COALESCE(SUM(CASE WHEN pi.is_tax = 0 THEN pi.amount ELSE 0 END), 0)
                    - COALESCE(SUM(CASE WHEN pi.is_tax = 1 THEN pi.amount ELSE 0 END), 0) AS net
            FROM {{%wage_payslips}} p
            LEFT JOIN {{%wage_payslip_items}} pi ON pi.payslip_id = p.id
            GROUP BY p.period_year
            ORDER BY p.period_year DESC
        ')
            ->queryAll();

        return array_map(
            static fn(array $row) => new YearlyStatDto(
                year:  (int)$row['period_year'],
                gross: (float)$row['gross'],
                tax:   (float)$row['tax'],
                net:   (float)$row['net'],
            ),
            $rows
        );
    }

    /**
     * Возвращает минимальный и максимальный годы имеющихся квитков.
     *
     * @return array{min: int, max: int}|null null если квитков нет
     */
    public function findYearBounds(): ?array
    {
        $row = Yii::$app->db->createCommand('
            SELECT MIN(period_year) AS min_year, MAX(period_year) AS max_year
            FROM {{%wage_payslips}}
        ')->queryOne();

        if ($row === false || $row['min_year'] === null) {
            return null;
        }

        return ['min' => (int)$row['min_year'], 'max' => (int)$row['max_year']];
    }
}
