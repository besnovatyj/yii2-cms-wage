<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage;

use common\components\module\CmsModule;
use modules\modmanNew\contract\DeclaresModule;
use modules\modmanNew\contract\ProvidesAdminMenu;
use modules\modmanNew\contract\ProvidesMigrations;
use Yii;

/**
 * Модуль учёта зарплатных квитков.
 * Предоставляет функциональность создания, редактирования и аналитики квитков.
 */
class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu,
    ProvidesMigrations
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'Wage';

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
    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function adminMenu(): array { return require __DIR__.'/config/adminMenu.php'; }
    public static function moduleConfig(): array { return require __DIR__.'/config/config.php'; }
    public static function migrationPath(): string { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }

}
