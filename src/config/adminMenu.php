<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

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
                [
                    'location'      => 'left-sidebar',
                    'group'         => 'Wage',
                    'groupIcon'     => 'bi bi-cash-coin',
                    'priority'      => 100,
                    'groupPriority' => 100,
                ],
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
                [
                    'location'      => 'left-sidebar',
                    'group'         => 'Wage',
                    'groupIcon'     => 'bi bi-cash-coin',
                    'priority'      => 200,
                    'groupPriority' => 100,
                ],
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
                [
                    'location'      => 'left-sidebar',
                    'group'         => 'Wage',
                    'groupIcon'     => 'bi bi-cash-coin',
                    'priority'      => 400,
                    'groupPriority' => 100,
                ],
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
                [
                    'location'      => 'left-sidebar',
                    'group'         => 'Wage',
                    'groupIcon'     => 'bi bi-cash-coin',
                    'priority'      => 300,
                    'groupPriority' => 100,
                ],
            ],
        ],
    ],
];
