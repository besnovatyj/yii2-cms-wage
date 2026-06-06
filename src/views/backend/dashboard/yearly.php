<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Wage\dto\YearlyStatDto;
use yii\helpers\Html;

/* @var $this        yii\web\View */
/* @var $yearlyStats YearlyStatDto[] */

$this->title                   = 'Сводка по годам';
$this->params['breadcrumbs'][] = ['label' => 'График зарплат', 'url' => ['/Wage/backend/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmt = fn(float $v) => number_format($v, 2, '.', ' ') . ' ₽';

// Итоги по всем годам
$grandGross = array_sum(array_map(fn(YearlyStatDto $d) => $d->gross, $yearlyStats));
$grandTax   = array_sum(array_map(fn(YearlyStatDto $d) => $d->tax,   $yearlyStats));
$grandNet   = array_sum(array_map(fn(YearlyStatDto $d) => $d->net,   $yearlyStats));
?>

<div class="wage-yearly">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            Сводка по годам
            <?= Html::a('<i class="bi bi-bar-chart-line me-1"></i>График',
                ['/Wage/backend/dashboard/index'],
                ['class' => 'btn btn-outline-secondary btn-sm']
            ) ?>
        </div>

        <?php if (empty($yearlyStats)): ?>
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-table fs-1 d-block mb-2"></i>
                Нет данных. <?= Html::a('Создать первый квиток', ['/Wage/backend/payslip/create'], ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Год</th>
                            <th class="text-end">Итого (gross)</th>
                            <th class="text-end text-danger">Налоги</th>
                            <th class="text-end text-success">На руки (net)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($yearlyStats as $stat): ?>
                            <tr>
                                <td>
                                    <?= Html::a(
                                        (string)$stat->year,
                                        ['/Wage/backend/dashboard/index', 'year_from' => $stat->year, 'year_to' => $stat->year],
                                        ['title' => 'Смотреть график за ' . $stat->year . ' год']
                                    ) ?>
                                </td>
                                <td class="text-end fw-semibold"><?= $fmt($stat->gross) ?></td>
                                <td class="text-end text-danger"><?= $fmt($stat->tax) ?></td>
                                <td class="text-end text-success fw-bold"><?= $fmt($stat->net) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Всего за все годы</td>
                            <td class="text-end"><?= $fmt($grandGross) ?></td>
                            <td class="text-end text-danger"><?= $fmt($grandTax) ?></td>
                            <td class="text-end text-success"><?= $fmt($grandNet) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif ?>
    </div>

</div>
