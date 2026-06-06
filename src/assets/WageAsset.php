<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\assets;

use yii\web\AssetBundle;

/**
 * Общий asset bundle модуля Wage.
 * Включает скрипт управления динамической формой квитка.
 */
class WageAsset extends AssetBundle
{
    /** @var string Путь к директории с собранными ресурсами */
    public $sourcePath = __DIR__ . '/dist';

    /** @var array JS-файлы */
    public $js = [
        'wage-form.js',
    ];

    /** @var array Зависимости */
    public $depends = [
        \yii\web\JqueryAsset::class,
    ];
}
