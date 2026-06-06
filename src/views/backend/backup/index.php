<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Страница бэкапа и восстановления данных квитков.
 *
 * @var yii\web\View $this
 */

use yii\helpers\Html;

$this->title = 'Бэкап данных';
?>
<div class="wage-backup-index">

    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <?php foreach (['success', 'warning', 'error'] as $type): ?>
        <?php if (Yii::$app->session->hasFlash($type)): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                <?= Html::encode(Yii::$app->session->getFlash($type)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
            </div>
        <?php endif ?>
    <?php endforeach ?>

    <div class="row g-4">

        <!-- Экспорт -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-download me-2"></i>Экспорт
                    </h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="text-muted flex-grow-1">
                        Скачает JSON-файл со всеми квитками и их строками.
                        Файл удобно читается и редактируется вручную.
                    </p>
                    <?= Html::a(
                        '<i class="bi bi-file-earmark-arrow-down me-1"></i> Скачать бэкап',
                        ['export'],
                        ['class' => 'btn btn-primary align-self-start']
                    ) ?>
                </div>
            </div>
        </div>

        <!-- Восстановление -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-upload me-2"></i>Восстановление
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Загрузите ранее экспортированный JSON-файл.
                        Квитки за периоды, которые уже существуют в базе, будут пропущены без изменений.
                    </p>
                    <form method="post" action="<?= \yii\helpers\Url::to(['import']) ?>" enctype="multipart/form-data">
                        <?= Html::hiddenInput(\Yii::$app->request->csrfParam, \Yii::$app->request->csrfToken) ?>
                        <div class="mb-3">
                            <label class="form-label" for="backup_file">JSON-файл бэкапа</label>
                            <input class="form-control" type="file" id="backup_file" name="backup_file" accept=".json" required>
                        </div>
                        <button type="submit" class="btn btn-warning"
                                onclick="return confirm('Восстановить данные из файла?\nСуществующие квитки будут пропущены.')">
                            <i class="bi bi-cloud-upload me-1"></i> Восстановить
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
