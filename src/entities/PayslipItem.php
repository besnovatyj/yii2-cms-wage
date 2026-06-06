<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\entities;

use Besnovatyj\Wage\entities\queries\PayslipItemQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Строка зарплатного квитка (статья выплат).
 *
 * @property int    $id
 * @property int    $payslip_id  FK на квиток
 * @property string $title       Название статьи
 * @property float  $amount      Сумма выплаты
 * @property bool   $is_tax      Признак налога
 * @property int    $sort_order  Порядок сортировки
 *
 * @property Payslip $payslip
 */
class PayslipItem extends ActiveRecord
{
    /**
     * Фабричный метод создания строки квитка.
     */
    public static function create(int $payslipId, string $title, float $amount, bool $isTax, int $sortOrder = 0): self
    {
        $item              = new static();
        $item->payslip_id  = $payslipId;
        $item->title       = $title;
        $item->amount      = $amount;
        $item->is_tax      = $isTax;
        $item->sort_order  = $sortOrder;
        return $item;
    }

    /**
     * Обновляет поля строки.
     */
    public function edit(string $title, float $amount, bool $isTax, int $sortOrder): void
    {
        $this->title      = $title;
        $this->amount     = $amount;
        $this->is_tax     = $isTax;
        $this->sort_order = $sortOrder;
    }

    // <editor-fold desc="Отношения">

    /** @return ActiveQuery<Payslip> */
    public function getPayslip(): ActiveQuery
    {
        return $this->hasOne(Payslip::class, ['id' => 'payslip_id']);
    }

    // </editor-fold>

    public static function tableName(): string
    {
        return '{{%wage_payslip_items}}';
    }

    public static function find(): PayslipItemQuery
    {
        return new PayslipItemQuery(static::class);
    }
}
