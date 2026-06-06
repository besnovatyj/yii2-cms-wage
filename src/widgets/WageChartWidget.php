<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\widgets;

use Besnovatyj\Wage\assets\WageChartAsset;
use Besnovatyj\Wage\dto\MonthlyStatDto;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * Виджет помесячного bar-графика зарплат.
 * Рендерит canvas и инициализирует WageBarChart через JS.
 *
 * Использование:
 * ```php
 * echo WageChartWidget::widget([
 *     'monthlyStats' => $monthlyStats, // MonthlyStatDto[]
 *     'height'       => 400,
 * ]);
 * ```
 */
class WageChartWidget extends Widget
{
    /**
     * Данные для графика — массив MonthlyStatDto.
     *
     * @var MonthlyStatDto[]
     */
    public array $monthlyStats = [];

    /**
     * Высота canvas в пикселях.
     */
    public int $height = 420;

    /**
     * Цвет столбца чистой суммы (CSS-цвет).
     */
    public string $barNetColor = '#4caf50';

    /**
     * Цвет столбца налогов (CSS-цвет).
     */
    public string $barTaxColor = '#f44336';

    /**
     * Символ валюты.
     */
    public string $currencySymbol = '₽';

    public function run(): string
    {
        WageChartAsset::register($this->view);

        $chartId = $this->getId() . '-chart';
        $data    = $this->buildChartData();
        $config  = Json::encode([
            'canvasId'       => $chartId,
            'data'           => $data,
            'height'         => $this->height,
            'barNetColor'    => $this->barNetColor,
            'barTaxColor'    => $this->barTaxColor,
            'currencySymbol' => $this->currencySymbol,
            'locale'         => 'ru-RU',
        ]);

        $this->view->registerJs("window.createWageChart({$config});");

        return $this->render('wage-chart', [
            'chartId' => $chartId,
            'height'  => $this->height,
        ]);
    }

    /**
     * Преобразует DTO-объекты в массив для передачи в JS.
     *
     * @return array<int, array{label: string, netAmount: float, taxAmount: float}>
     */
    private function buildChartData(): array
    {
        return array_map(
            static fn(MonthlyStatDto $dto) => [
                'label'     => $dto->getChartLabel(),
                'netAmount' => $dto->net,
                'taxAmount' => $dto->tax,
            ],
            $this->monthlyStats
        );
    }
}
