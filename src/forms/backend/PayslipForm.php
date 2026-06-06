<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\forms\backend;

use Besnovatyj\Forms\BaseForm;
use Besnovatyj\Wage\entities\Payslip;
use yii\base\Model;

/**
 * Форма создания/редактирования зарплатного квитка.
 * Содержит основные реквизиты квитка и массив строк.
 */
class PayslipForm extends BaseForm
{
    public int    $year;
    public int    $month;
    public ?string $note = '';

    /** @var PayslipItemForm[] */
    public array $items = [];

    public function __construct(?Payslip $payslip = null, array $config = [])
    {
        if ($payslip !== null) {
            $this->year  = $payslip->period_year;
            $this->month = $payslip->period_month;
            $this->note  = (string)$payslip->note;
            $this->items = array_map(
                static fn($item) => new PayslipItemForm($item),
                $payslip->items
            );
        } else {
            $this->year  = (int)date('Y');
            $this->month = (int)date('n');
            // Добавляем одну пустую строку по умолчанию
            $this->items = [new PayslipItemForm()];
        }

        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['year', 'month'], 'required'],
            [['year'], 'integer', 'min' => 2000, 'max' => 2100],
            [['month'], 'integer', 'min' => 1, 'max' => 12],
            [['note'], 'string', 'max' => 1000],
            [['note'], 'default', 'value' => null],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'year'  => 'Год',
            'month' => 'Месяц',
            'note'  => 'Примечание',
        ];
    }

    /**
     * Загружает строки квитка из POST-данных.
     *
     * @param array $rawItems Массив данных строк (из $_POST['PayslipItemForm'])
     */
    public function loadItems(array $rawItems): void
    {
        $this->items = [];
        foreach ($rawItems as $index => $itemData) {
            $item = new PayslipItemForm();
            $item->load($itemData, '');
            $item->sort_order = (int)$index;
            $this->items[]    = $item;
        }
    }

    /**
     * Валидация формы включая все строки.
     */
    public function validate($attributeNames = null, $clearErrors = true): bool
    {
        $valid = parent::validate($attributeNames, $clearErrors);

        foreach ($this->items as $item) {
            if (!$item->validate()) {
                $valid = false;
            }
        }

        if (empty($this->items)) {
            $this->addError('items', 'Квиток должен содержать хотя бы одну строку.');
            $valid = false;
        }

        return $valid;
    }
}
