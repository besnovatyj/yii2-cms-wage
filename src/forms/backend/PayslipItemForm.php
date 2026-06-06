<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\forms\backend;

use Besnovatyj\Forms\BaseForm;
use Besnovatyj\Wage\entities\PayslipItem;
use yii\base\Model;

/**
 * Подформа одной строки зарплатного квитка.
 */
class PayslipItemForm extends BaseForm
{
    public string $title      = '';
    public string $amount     = '';   // строка, чтобы корректно обрабатывать пользовательский ввод с запятой/точкой
    public bool   $is_tax     = false;
    public int    $sort_order = 0;

    public function __construct(PayslipItem $item = null, array $config = [])
    {
        if ($item !== null) {
            $this->title      = $item->title;
            $this->amount     = (string)$item->amount;
            $this->is_tax     = (bool)$item->is_tax;
            $this->sort_order = $item->sort_order;
        }
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['amount'], 'required'],
            [['amount'], 'number', 'min' => 0],
            [['is_tax'], 'boolean'],
            [['sort_order'], 'integer', 'min' => 0],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title'      => 'Статья',
            'amount'     => 'Сумма',
            'is_tax'     => 'Налог',
            'sort_order' => 'Порядок',
        ];
    }

    /**
     * Возвращает сумму в виде float (с заменой запятой на точку).
     */
    public function getAmountFloat(): float
    {
        return (float)str_replace(',', '.', $this->amount);
    }
}
