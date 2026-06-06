<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\entities;

use Besnovatyj\Wage\entities\queries\PayslipQuery;
use DomainException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Зарплатный квиток (запись за месяц).
 *
 * @property int         $id
 * @property int         $period_year   Год периода
 * @property int         $period_month  Месяц периода (1-12)
 * @property string|null $note          Примечание
 * @property string      $created_at
 * @property string      $updated_at
 *
 * @property PayslipItem[] $items
 */
class Payslip extends ActiveRecord
{
    /**
     * Фабричный метод создания нового квитка.
     */
    public static function create(int $year, int $month, ?string $note): self
    {
        $payslip = new static();
        $payslip->period_year  = $year;
        $payslip->period_month = $month;
        $payslip->note         = $note;
        return $payslip;
    }

    /**
     * Редактирует реквизиты квитка (без строк — строки управляются через PayslipManageService).
     */
    public function edit(int $year, int $month, ?string $note): void
    {
        $this->period_year  = $year;
        $this->period_month = $month;
        $this->note         = $note;
    }

    // <editor-fold desc="Вычисляемые поля">

    /**
     * Возвращает итоговую начисленную сумму квитка (без налогов).
     */
    public function getGrossAmount(): float
    {
        return (float)array_sum(
            array_map(
                fn(PayslipItem $i) => $i->amount,
                array_filter($this->items, fn(PayslipItem $i) => !$i->is_tax)
            )
        );
    }

    /**
     * Возвращает сумму налогов.
     */
    public function getTaxAmount(): float
    {
        return (float)array_sum(
            array_map(
                fn(PayslipItem $i) => $i->amount,
                array_filter($this->items, fn(PayslipItem $i) => $i->is_tax)
            )
        );
    }

    /**
     * Возвращает сумму за вычетом налогов.
     */
    public function getNetAmount(): float
    {
        return $this->getGrossAmount() - $this->getTaxAmount();
    }

    /**
     * Возвращает читаемый период вида «Март 2025».
     */
    public function getPeriodLabel(): string
    {
        $months = [
            1  => 'Январь',
            2  => 'Февраль',
            3  => 'Март',
            4  => 'Апрель',
            5  => 'Май',
            6  => 'Июнь',
            7  => 'Июль',
            8  => 'Август',
            9  => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        ];

        return ($months[$this->period_month] ?? '?') . ' ' . $this->period_year;
    }

    // </editor-fold>

    // <editor-fold desc="Отношения">

    /** @return ActiveQuery<PayslipItem> */
    public function getItems(): ActiveQuery
    {
        return $this->hasMany(PayslipItem::class, ['payslip_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    // </editor-fold>

    public static function tableName(): string
    {
        return '{{%wage_payslips}}';
    }

    public static function find(): PayslipQuery
    {
        return new PayslipQuery(static::class);
    }

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }
}
