<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\forms\backend\search;

use Besnovatyj\Wage\entities\Payslip;
use yii\data\ActiveDataProvider;

/**
 * Поисковая форма для грида квитков.
 */
class PayslipSearch extends Payslip
{
    /** @var int|string|null Фильтр по году */
    public int|string|null $search_year = null;

    /** @var int|string|null Фильтр по месяцу */
    public int|string|null $search_month = null;

    public function beforeValidate(): bool
    {
        if ($this->search_year) {
            $this->search_year = intval($this->search_year);
        } else {
            $this->search_year = null;
        }
        if ($this->search_month) {
            $this->search_month = intval($this->search_month);
        } else {
            $this->search_month = null;
        }
        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            [['search_year'], 'integer', 'min' => 2000, 'max' => 2100],
            [['search_month'], 'integer', 'min' => 1, 'max' => 12],
        ];
    }

    public function scenarios(): array
    {
        return \yii\base\Model::scenarios();
    }

    /**
     * Строит DataProvider для GridView.
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Payslip::find()->latestFirst();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 25],
            'sort' => [
                'defaultOrder' => ['period_year' => SORT_DESC, 'period_month' => SORT_DESC],
                'attributes' => ['period_year', 'period_month'],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->search_year !== null) {
            $query->andWhere(['period_year' => $this->search_year]);
        }

        if ($this->search_month !== null) {
            $query->andWhere(['period_month' => $this->search_month]);
        }

        return $dataProvider;
    }

    /**
     * Список годов для выпадающего фильтра.
     *
     * @return array<int, string>
     */
    public function yearsList(): array
    {
        $current = (int)date('Y');
        $result = [];
        for ($y = $current; $y >= 2009; $y--) {
            $result[$y] = (string)$y;
        }
        return $result;
    }

    /**
     * Список месяцев для выпадающего фильтра.
     *
     * @return array<int, string>
     */
    public function monthsList(): array
    {
        return [
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        ];
    }
}
