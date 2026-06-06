<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/**
 * Добавляет внешние ключи между таблицами модуля Wage.
 */
class m260312_000003_create_wage_foreign_keys extends BaseMigration
{
    public const string TABLE_NAME = '{{%wage_payslip_items}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        $this->createFKs(
            '{{%wage_payslip_items}}',
            'payslip_id',
            '{{%wage_payslips}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * @throws NotSupportedException
     */
    public function safeDown(): void
    {
        parent::safeDown();
    }
}
