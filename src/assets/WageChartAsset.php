<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle графика зарплат.
 * Публикует скомпилированный JS-файл виджета WageChartWidget.
 */
class WageChartAsset extends AssetBundle
{
    /** @var string Путь к директории с собранными ресурсами */
    public $sourcePath = __DIR__ . '/dist';

    /** @var array JS-файлы */
    public $js = [
        'wage-chart.js',
    ];

    /** @var array Зависимости (только стандартные Yii-ассеты) */
    public $depends = [
        \yii\web\JqueryAsset::class,
    ];
}
