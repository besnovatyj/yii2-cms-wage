<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\migrations;

use common\components\migration\BaseMigration;
use yii\base\NotSupportedException;

/**
 * Создаёт таблицу строк зарплатного квитка (статьи выплат).
 */
class m260312_000002_create_wage_payslip_items_table extends BaseMigration
{
    public const string TABLE_NAME = '{{%wage_payslip_items}}';

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
            'id'         => $this->primaryKey()->unsigned()->comment('Идентификатор строки'),
            'payslip_id' => $this->integer()->unsigned()->notNull()->comment('FK на квиток'),
            'title'      => $this->string(255)->notNull()->comment('Название статьи выплаты'),
            'amount'     => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('Сумма выплаты по статье'),
            'is_tax'     => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('Признак налога (1 - налог, 0 - выплата)'),
            'sort_order' => $this->smallInteger()->notNull()->defaultValue(0)->comment('Порядок отображения строки в квитке'),
        ], $this->tableOptions);

        $this->addCommentOnTable(static::TABLE_NAME, 'Строки зарплатных квитков (статьи выплат)');

        $this->createIndexes(static::TABLE_NAME, 'payslip_id');
        $this->createIndexes(static::TABLE_NAME, 'is_tax');
        $this->createIndexes(static::TABLE_NAME, 'sort_order');
    }

    /**
     * @throws NotSupportedException
     */
    public function safeDown(): void
    {
        parent::safeDown();
    }
}
