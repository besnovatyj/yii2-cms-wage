<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\forms\backend\search\PayslipSearch;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this         yii\web\View */
/* @var $searchModel  PayslipSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title                   = 'Квитки';
$this->params['breadcrumbs'][] = $this->title;

$fmt = fn(float $v) => number_format($v, 2, '.', ' ') . ' ₽';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        Список квитков
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i>Создать квиток',
            ['create'],
            ['class' => 'btn btn-primary btn-sm']
        ) ?>
    </div>
    <div class="card-body p-0">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'layout'       => "{summary}\n{items}",
            'tableOptions' => ['class' => 'table table-hover table-bordered mb-0'],
            'columns'      => [
                [
                    'attribute'      => 'period_year',
                    'label'          => 'Год',
                    'filter'         => Html::activeDropDownList(
                        $searchModel, 'search_year',
                        $searchModel->yearsList(),
                        ['class' => 'form-select form-select-sm', 'prompt' => 'Все']
                    ),
                    'value'          => 'period_year',
                    'contentOptions' => ['style' => 'width:80px'],
                ],
                [
                    'attribute'      => 'period_month',
                    'label'          => 'Месяц',
                    'filter'         => Html::activeDropDownList(
                        $searchModel, 'search_month',
                        $searchModel->monthsList(),
                        ['class' => 'form-select form-select-sm', 'prompt' => 'Все']
                    ),
                    'value'          => static fn(Payslip $p) => $p->getPeriodLabel(),
                    'contentOptions' => ['style' => 'width:140px'],
                ],
                [
                    'label'  => 'Итого (gross)',
                    'value'  => static fn(Payslip $p) => $fmt($p->getGrossAmount()),
                    'contentOptions' => ['class' => 'text-end fw-semibold'],
                    'headerOptions'  => ['class' => 'text-end'],
                ],
                [
                    'label'          => 'Налог',
                    'value'          => static fn(Payslip $p) => $fmt($p->getTaxAmount()),
                    'contentOptions' => ['class' => 'text-end text-danger'],
                    'headerOptions'  => ['class' => 'text-end'],
                ],
                [
                    'label'          => 'На руки (net)',
                    'value'          => static fn(Payslip $p) => $fmt($p->getNetAmount()),
                    'contentOptions' => ['class' => 'text-end text-success fw-bold'],
                    'headerOptions'  => ['class' => 'text-end'],
                ],
                [
                    'label'          => 'Строк',
                    'value'          => static fn(Payslip $p) => count($p->items),
                    'contentOptions' => ['class' => 'text-center', 'style' => 'width:70px'],
                    'headerOptions'  => ['class' => 'text-center'],
                ],
                [
                    'class'          => ActionColumn::class,
                    'template'       => '{view} {update} {delete}',
                    'buttons'        => [
                        'view'   => static fn($url, Payslip $p) => Html::a(
                            '<i class="bi bi-eye"></i>', ['view', 'id' => $p->id],
                            ['class' => 'btn btn-sm btn-outline-secondary me-1', 'title' => 'Просмотр']
                        ),
                        'update' => static fn($url, Payslip $p) => Html::a(
                            '<i class="bi bi-pencil"></i>', ['update', 'id' => $p->id],
                            ['class' => 'btn btn-sm btn-outline-primary me-1', 'title' => 'Редактировать']
                        ),
                        'delete' => static fn($url, Payslip $p) => Html::a(
                            '<i class="bi bi-trash"></i>', ['delete', 'id' => $p->id],
                            [
                                'class'             => 'btn btn-sm btn-outline-danger',
                                'title'             => 'Удалить',
                                'data-confirm'      => "Удалить квиток «{$p->getPeriodLabel()}»?",
                                'data-method'       => 'post',
                            ]
                        ),
                    ],
                    'contentOptions' => ['style' => 'width:120px; white-space:nowrap'],
                ],
            ],
        ]) ?>
    </div>
    <div class="card-footer">
        <?= LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
    </div>
</div>
