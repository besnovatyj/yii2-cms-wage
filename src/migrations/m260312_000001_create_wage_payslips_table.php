<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/**
 * Создаёт таблицу зарплатных квитков.
 */
class m260312_000001_create_wage_payslips_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%wage_payslips}}';

    /**
     * @throws NotSupportedException
     */
    public function safeUp(): void
    {
        parent::safeUp();

        if ($this->existTable(static::TABLE_NAME)) {
            return;
        }

        $this->createTable(static::TABLE_NAME, [
            'id'           => $this->primaryKey()->unsigned()->comment('Идентификатор квитка'),
            'period_year'  => $this->smallInteger()->notNull()->comment('Год периода'),
            'period_month' => $this->tinyInteger()->notNull()->comment('Месяц периода (1-12)'),
            'note'         => $this->text()->null()->comment('Произвольное примечание к квитку'),
            'created_at'   => $this->dateTime()->notNull()->defaultExpression('NOW()')->comment('Дата создания записи'),
            'updated_at'   => $this->dateTime()->notNull()->defaultExpression('NOW()')->append('ON UPDATE NOW()')->comment('Дата обновления записи'),
        ], $this->tableOptions);

        $this->addCommentOnTable(static::TABLE_NAME, 'Зарплатные квитки');

        $this->createIndexes(static::TABLE_NAME, ['period_year', 'period_month'], false, true);
        $this->createIndexes(static::TABLE_NAME, 'period_year');
    }

    /**
     * @throws NotSupportedException
     */
    public function safeDown(): void
    {
        parent::safeDown();
    }
}
