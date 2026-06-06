<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Wage\dto\MonthlyStatDto;
use Besnovatyj\Wage\widgets\WageChartWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this         yii\web\View */
/* @var $monthlyStats MonthlyStatDto[] */
/* @var $yearFrom     int */
/* @var $yearTo       int */
/* @var $yearBounds   array{min:int,max:int}|null */
/* @var $currentYear  int */

$this->title                    = 'График зарплат';
$this->params['breadcrumbs'][]  = $this->title;

$minYear = $yearBounds['min'] ?? ($currentYear - 5);
$maxYear = $yearBounds['max'] ?? $currentYear;

// Строим список годов для селекта
$years = [];
for ($y = $maxYear; $y >= $minYear; $y--) {
    $years[$y] = (string)$y;
}

// Итоговые суммы за период
$totalGross = array_sum(array_map(fn(MonthlyStatDto $d) => $d->gross, $monthlyStats));
$totalTax   = array_sum(array_map(fn(MonthlyStatDto $d) => $d->tax,   $monthlyStats));
$totalNet   = array_sum(array_map(fn(MonthlyStatDto $d) => $d->net,   $monthlyStats));

$fmt = fn(float $v) => number_format($v, 2, '.', ' ') . ' ₽';
?>

<div class="wage-dashboard">

    <!-- Фильтр периода -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= Url::to(['/Wage/backend/dashboard/index']) ?>" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1">Год с</label>
                    <?= Html::dropDownList('year_from', $yearFrom, $years, ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">по</label>
                    <?= Html::dropDownList('year_to', $yearTo, $years, ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Показать</button>
                </div>
                <div class="col-auto">
                    <?= Html::a('Сбросить', ['/Wage/backend/dashboard/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Итоговые карточки за период -->
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card text-bg-light h-100">
                <div class="card-body text-center">
                    <div class="fs-6 text-muted">Итого (gross)</div>
                    <div class="fs-4 fw-bold"><?= $fmt($totalGross) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <div class="fs-6 text-muted">Налоги</div>
                    <div class="fs-4 fw-bold text-danger"><?= $fmt($totalTax) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-bg-success bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <div class="fs-6 text-muted">На руки (net)</div>
                    <div class="fs-4 fw-bold text-success"><?= $fmt($totalNet) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- График -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                Помесячно: <?= $yearFrom ?> – <?= $yearTo ?>
                <span class="badge bg-secondary ms-2"><?= count($monthlyStats) ?> мес.</span>
            </span>
            <?= Html::a('<i class="bi bi-table me-1"></i>Сводка по годам',
                ['/Wage/backend/dashboard/yearly'],
                ['class' => 'btn btn-outline-secondary btn-sm']
            ) ?>
        </div>
        <div class="card-body p-2">
            <?php if (empty($monthlyStats)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                    Нет квитков за выбранный период.
                    <?= Html::a('Создать квиток', ['/Wage/backend/payslip/create'], ['class' => 'btn btn-primary btn-sm ms-2']) ?>
                </div>
            <?php else: ?>
                <?= WageChartWidget::widget([
                    'monthlyStats' => $monthlyStats,
                    'height'       => 420,
                ]) ?>
            <?php endif ?>
        </div>
    </div>

</div>
