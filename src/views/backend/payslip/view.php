<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\entities\PayslipItem;
use yii\helpers\Html;

/* @var $this    yii\web\View */
/* @var $payslip Payslip */

$this->title                   = 'Квиток: ' . $payslip->getPeriodLabel();
$this->params['breadcrumbs'][] = ['label' => 'Квитки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$fmt = fn(float $v) => number_format($v, 2, '.', ' ') . ' ₽';
?>

<div class="wage-payslip-view">

    <!-- Управление -->
    <div class="mb-3 d-flex gap-2">
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i>Создать квиток',
            ['create'],
            ['class' => 'btn btn-primary btn-sm']
        ) ?>
        <?= Html::a('<i class="bi bi-pencil me-1"></i>Редактировать', ['update', 'id' => $payslip->id], ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::a('<i class="bi bi-trash me-1"></i>Удалить', ['delete', 'id' => $payslip->id], [
            'class'        => 'btn btn-danger btn-sm',
            'data-confirm' => 'Удалить квиток «' . $payslip->getPeriodLabel() . '»?',
            'data-method'  => 'post',
        ]) ?>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>К списку', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>

    <div class="row g-3">

        <!-- Реквизиты квитка -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header fw-semibold">Реквизиты</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Период</dt>
                        <dd class="col-sm-7"><?= Html::encode($payslip->getPeriodLabel()) ?></dd>

                        <dt class="col-sm-5">Год</dt>
                        <dd class="col-sm-7"><?= $payslip->period_year ?></dd>

                        <dt class="col-sm-5">Месяц</dt>
                        <dd class="col-sm-7"><?= $payslip->period_month ?></dd>

                        <?php if ($payslip->note): ?>
                            <dt class="col-sm-5">Примечание</dt>
                            <dd class="col-sm-7"><?= nl2br(Html::encode($payslip->note)) ?></dd>
                        <?php endif ?>

                        <dt class="col-sm-5">Создан</dt>
                        <dd class="col-sm-7"><small class="text-muted"><?= Yii::$app->formatter->asDatetime($payslip->created_at) ?></small></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Итоги -->
        <div class="col-md-8">
            <div class="row g-2 mb-3">
                <div class="col-sm-4">
                    <div class="card text-bg-light text-center">
                        <div class="card-body py-2">
                            <div class="small text-muted">Итого</div>
                            <div class="fw-bold"><?= $fmt($payslip->getGrossAmount()) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card text-center border-danger">
                        <div class="card-body py-2">
                            <div class="small text-muted">Налоги</div>
                            <div class="fw-bold text-danger"><?= $fmt($payslip->getTaxAmount()) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card text-center border-success">
                        <div class="card-body py-2">
                            <div class="small text-muted">На руки</div>
                            <div class="fw-bold text-success"><?= $fmt($payslip->getNetAmount()) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Строки квитка -->
            <div class="card">
                <div class="card-header">Статьи выплат (<?= count($payslip->items) ?>)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Статья</th>
                                <th class="text-end">Сумма</th>
                                <th class="text-center">Налог</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payslip->items as $i => $item): /** @var PayslipItem $item */ ?>
                                <tr class="<?= $item->is_tax ? 'table-danger' : '' ?>">
                                    <td class="text-muted small"><?= $i + 1 ?></td>
                                    <td><?= Html::encode($item->title) ?></td>
                                    <td class="text-end fw-semibold"><?= $fmt((float)$item->amount) ?></td>
                                    <td class="text-center">
                                        <?php if ($item->is_tax): ?>
                                            <span class="badge bg-danger">налог</span>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Итого:</td>
                                <td class="text-end"><?= $fmt($payslip->getGrossAmount()) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
