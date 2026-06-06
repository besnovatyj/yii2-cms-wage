<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage;

use common\components\module\BaseModule;
use Yii;

/**
 * Модуль учёта зарплатных квитков.
 * Предоставляет функциональность создания, редактирования и аналитики квитков.
 */
class Module extends BaseModule
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';

    public static function getAdminMenu(): array
    {
        return require __DIR__ . '/config/adminMenu.php';
    }

    public static function getConfig(): array
    {
        return require __DIR__ . '/config/config.php';
    }

    public static function getOptions(): array
    {
        return require __DIR__ . '/config/options.php';
    }

    public static function getDependencies(): array
    {
        return require __DIR__ . '/config/dependencies.php';
    }

    public function init(): void
    {
        parent::init();

        if (!isset(Yii::$app->i18n->translations['Wage'])) {
            Yii::$app->i18n->translations['Wage'] = [
                'class'          => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'ru',
                'basePath'       => '@Besnovatyj/Wage/messages',
            ];
        }
    }
}
