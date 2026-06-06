<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\controllers\backend;

use Besnovatyj\Wage\readModels\PayslipReadRepository;
use Yii;
use yii\base\Module;
use yii\web\Controller;

/**
 * Контроллер дашборда зарплат.
 * Предоставляет помесячный график и годовую сводку.
 */
class DashboardController extends Controller
{
    public function __construct(
        string                            $id,
        Module                  $module,
        private readonly PayslipReadRepository $readRepository,
        array                             $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Страница с помесячным графиком за выбранный диапазон годов.
     * По умолчанию — последние 12 месяцев (предыдущий и текущий год).
     */
    public function actionIndex(): string
    {
        $currentYear = (int)date('Y');
        $yearFrom    = (int)Yii::$app->request->get('year_from', $currentYear - 1);
        $yearTo      = (int)Yii::$app->request->get('year_to', $currentYear);

        // Ограничиваем разумными пределами
        $yearFrom = max(2000, min($yearFrom, $yearTo));
        $yearTo   = min(2100, max($yearTo, $yearFrom));

        $monthlyStats = $this->readRepository->findMonthlyStats($yearFrom, $yearTo);
        $yearBounds   = $this->readRepository->findYearBounds();

        return $this->render('index', [
            'monthlyStats' => $monthlyStats,
            'yearFrom'     => $yearFrom,
            'yearTo'       => $yearTo,
            'yearBounds'   => $yearBounds,
            'currentYear'  => $currentYear,
        ]);
    }

    /**
     * Страница годовой сводки.
     */
    public function actionYearly(): string
    {
        $yearlyStats = $this->readRepository->findYearlyStats();

        return $this->render('yearly', [
            'yearlyStats' => $yearlyStats,
        ]);
    }
}
