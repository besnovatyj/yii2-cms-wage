<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use yii\helpers\Html;

/**
 * @var yii\web\View                                         $this
 * @var array<int, array{
 *     old_id: int,
 *     year: int,
 *     month: int,
 *     items: list<array{title: string, amount: float, is_tax: bool}>,
 *     exists: bool,
 *     error: string|null
 * }> $preview
 * @var bool $tableExists
 */

$this->title = 'Импорт из старой БД (wage_months)';

$monthNames = [
    1  => 'Январь',   2  => 'Февраль',  3  => 'Март',
    4  => 'Апрель',   5  => 'Май',      6  => 'Июнь',
    7  => 'Июль',     8  => 'Август',   9  => 'Сентябрь',
    10 => 'Октябрь',  11 => 'Ноябрь',  12 => 'Декабрь',
];
?>

<div class="wage-import">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-warning">
        <strong>Одноразовый инструмент!</strong>
        После успешного импорта удалите <code>ImportController.php</code> и папку
        <code>views/backend/import/</code> из проекта.
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning">
            <?= Html::encode(Yii::$app->session->getFlash('warning')) ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
        </div>
    <?php endif; ?>

    <?php
    /** @var list<array{year: int, month: int}> $skippedRows */
    $skippedRows = Yii::$app->session->getFlash('skippedRows', []);
    if (!empty($skippedRows)):
    ?>
        <div class="alert alert-info">
            <strong>Пропущенные дубликаты (<?= count($skippedRows) ?>):</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($skippedRows as $s): ?>
                    <li>
                        <?= Html::encode(($monthNames[$s['month']] ?? '?') . ' ' . $s['year']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$tableExists): ?>
        <div class="alert alert-danger">
            <strong>Таблица <code>wage_months</code> не найдена в текущей базе данных.</strong><br>
            Загрузите дамп старой БД и обновите страницу.
        </div>
    <?php elseif (empty($preview)): ?>
        <div class="alert alert-info">
            Таблица <code>wage_months</code> найдена, но не содержит данных.
        </div>
    <?php else: ?>

        <?php
        $toImport   = count(array_filter($preview, fn($r) => !$r['exists'] && $r['error'] === null));
        $toSkip     = count(array_filter($preview, fn($r) => $r['exists']));
        $withErrors = count(array_filter($preview, fn($r) => $r['error'] !== null));
        ?>

        <div class="row mb-3">
            <div class="col-auto">
                <span class="badge bg-secondary fs-6">Всего в дампе: <?= count($preview) ?></span>
            </div>
            <div class="col-auto">
                <span class="badge bg-success fs-6">Будет импортировано: <?= $toImport ?></span>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary fs-6">Уже существуют: <?= $toSkip ?></span>
            </div>
            <?php if ($withErrors > 0): ?>
                <div class="col-auto">
                    <span class="badge bg-danger fs-6">Ошибки парсинга: <?= $withErrors ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($toImport > 0): ?>
            <?= Html::beginForm(['run'], 'post') ?>
                <?= Html::submitButton(
                    'Запустить импорт (' . $toImport . ' ' . ($toImport === 1 ? 'запись' : 'записей') . ')',
                    [
                        'class' => 'btn btn-primary mb-4',
                        'data-confirm' => 'Запустить импорт ' . $toImport . ' квитков? Это действие нельзя отменить автоматически.',
                    ]
                ) ?>
            <?= Html::endForm() ?>
        <?php else: ?>
            <p class="text-muted mb-4">Нет новых записей для импорта.</p>
        <?php endif; ?>

        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:80px">old_id</th>
                    <th style="width:150px">Период</th>
                    <th style="width:80px">Статей</th>
                    <th style="width:150px">Статус</th>
                    <th>Статьи (превью)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($preview as $row): ?>
                    <?php
                    $rowClass = match (true) {
                        $row['error'] !== null => 'table-danger',
                        $row['exists']         => 'table-secondary',
                        default                => 'table-success',
                    };
                    $periodLabel = ($monthNames[$row['month']] ?? '?') . ' ' . $row['year'];
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= $row['old_id'] ?></td>
                        <td><?= Html::encode($periodLabel) ?></td>
                        <td><?= count($row['items']) ?></td>
                        <td>
                            <?php if ($row['error'] !== null): ?>
                                <span class="badge bg-danger">Ошибка парсинга</span>
                                <div class="small text-danger mt-1"><?= Html::encode($row['error']) ?></div>
                            <?php elseif ($row['exists']): ?>
                                <span class="badge bg-secondary">Уже существует</span>
                            <?php else: ?>
                                <span class="badge bg-success">Будет импортирован</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['items'])): ?>
                                <ul class="mb-0 ps-3 small">
                                    <?php foreach ($row['items'] as $item): ?>
                                        <li>
                                            <?= Html::encode($item['title']) ?>
                                            &mdash;
                                            <strong>
                                                <?= $item['is_tax'] ? '−' : '' ?>
                                                <?= number_format($item['amount'], 2, '.', '&nbsp;') ?>
                                            </strong>
                                            <?php if ($item['is_tax']): ?>
                                                <span class="badge bg-warning text-dark">налог/вычет</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>
