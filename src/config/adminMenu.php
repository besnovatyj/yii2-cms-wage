<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Contracts\adminMenu\AdminMenuLocation;
use Besnovatyj\Contracts\adminMenu\AdminMenuPlacement;

return [
    // Дашборд: помесячный график
    [
        'label'     => 'График зарплат',
        'iconClass' => 'bi bi-bar-chart-line me-1',
        'url'       => ['/Wage/backend/dashboard/index'],
        'active'    => static function () {
            return str_contains(Yii::$app->request->url, '/Wage/backend/dashboard/index');
        },
        '_meta' => [
            'placements' => [
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Wage',
                    groupIcon: 'bi bi-cash-coin',
                    groupPriority: 100,
                    priority: 100,
                ),
            ],
        ],
    ],

    // Сводка по годам
    [
        'label'     => 'Сводка по годам',
        'iconClass' => 'bi bi-table me-1',
        'url'       => ['/Wage/backend/dashboard/yearly'],
        'active'    => static function () {
            return str_contains(Yii::$app->request->url, '/Wage/backend/dashboard/yearly');
        },
        '_meta' => [
            'placements' => [
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Wage',
                    groupIcon: 'bi bi-cash-coin',
                    groupPriority: 100,
                    priority: 200,
                ),
            ],
        ],
    ],

    // Бэкап
    [
        'label'     => 'Бэкап',
        'iconClass' => 'bi bi-archive me-1',
        'url'       => ['/Wage/backend/backup/index'],
        'active'    => static function () {
            return str_contains(Yii::$app->request->url, '/Wage/backend/backup');
        },
        '_meta' => [
            'placements' => [
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Wage',
                    groupIcon: 'bi bi-cash-coin',
                    groupPriority: 100,
                    priority: 400,
                ),
            ],
        ],
    ],

    // Список квитков
    [
        'label'     => 'Квитки',
        'iconClass' => 'bi bi-receipt me-1',
        'url'       => ['/Wage/backend/payslip/index'],
        'active'    => static function () {
            return str_contains(Yii::$app->request->url, '/Wage/backend/payslip');
        },
        '_meta' => [
            'placements' => [
                new AdminMenuPlacement(
                    location: AdminMenuLocation::LeftSidebar,
                    group: 'Wage',
                    groupIcon: 'bi bi-cash-coin',
                    groupPriority: 100,
                    priority: 300,
                ),
            ],
        ],
    ],
];
