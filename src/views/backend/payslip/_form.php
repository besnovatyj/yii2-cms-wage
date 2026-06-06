<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Wage\assets\WageAsset;
use Besnovatyj\Wage\forms\backend\PayslipForm;
use Besnovatyj\Wage\forms\backend\PayslipItemForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this  yii\web\View */
/* @var $model PayslipForm  */

WageAsset::register($this);

// Список месяцев
$months = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март',
    4 => 'Апрель', 5 => 'Май',     6 => 'Июнь',
    7 => 'Июль',   8 => 'Август',  9 => 'Сентябрь',
    10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь',
];

// Список годов
$currentYear = (int)date('Y');
$years = [];
for ($y = $currentYear + 1; $y >= 2010; $y--) {
    $years[$y] = (string)$y;
}

// HTML-шаблон строки для JS (индекс __IDX__ будет заменён)
ob_start();
?>
<td class="text-muted small align-middle ps-2"><span class="row-num"></span></td>
<td>
    <input type="text"
           name="PayslipItemForm[__IDX__][title]"
           class="form-control form-control-sm"
           placeholder="Название статьи"
           required>
</td>
<td>
    <input type="number"
           name="PayslipItemForm[__IDX__][amount]"
           class="form-control form-control-sm text-end"
           placeholder="0.00"
           step="0.01" min="0"
           required>
</td>
<td class="text-center align-middle">
    <input type="checkbox"
           name="PayslipItemForm[__IDX__][is_tax]"
           class="form-check-input"
           value="1">
</td>
<td class="text-center align-middle">
    <input type="hidden"
           name="PayslipItemForm[__IDX__][sort_order]"
           value="__IDX__"
           data-sort-order>
    <button type="button"
            class="btn btn-outline-danger btn-sm py-0 px-1"
            data-wage-remove-item
            title="Удалить строку">
        <i class="bi bi-x-lg"></i>
    </button>
</td>
<?php
$rowTemplate = trim(ob_get_clean());
?>

<?php $form = ActiveForm::begin([
    'id'      => 'wage-payslip-form',
    'options' => ['data-wage-form' => true],
]) ?>

    <!-- Реквизиты квитка -->
    <div class="card mb-3">
        <div class="card-header fw-semibold">Реквизиты квитка</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <?= $form->field($model, 'year')->dropDownList($years, ['class' => 'form-select']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'month')->dropDownList($months, ['class' => 'form-select']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'note')->textarea(['rows' => 2, 'placeholder' => 'Необязательное примечание']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Строки квитка -->
    <div class="card mb-3">
        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
            <span>Статьи выплат</span>
            <button type="button"
                    class="btn btn-success btn-sm"
                    data-wage-add-item
                    data-row-template="<?= Html::encode($rowTemplate) ?>">
                <i class="bi bi-plus-lg me-1"></i>Добавить строку
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Статья</th>
                        <th style="width:160px" class="text-end">Сумма, ₽</th>
                        <th style="width:80px" class="text-center">Налог</th>
                        <th style="width:60px" class="text-center"></th>
                    </tr>
                </thead>
                <tbody data-wage-items>
                    <?php foreach ($model->items as $idx => $item): /** @var PayslipItemForm $item */ ?>
                        <tr data-wage-row="<?= $idx ?>">
                            <td class="text-muted small align-middle ps-2"><?= $idx + 1 ?></td>
                            <td>
                                <?= Html::input('text', "PayslipItemForm[{$idx}][title]", $item->title, [
                                    'class'       => 'form-control form-control-sm',
                                    'placeholder' => 'Название статьи',
                                    'required'    => true,
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::input('number', "PayslipItemForm[{$idx}][amount]", $item->amount, [
                                    'class'       => 'form-control form-control-sm text-end',
                                    'placeholder' => '0.00',
                                    'step'        => '0.01',
                                    'min'         => '0',
                                    'required'    => true,
                                ]) ?>
                            </td>
                            <td class="text-center align-middle">
                                <?= Html::checkbox("PayslipItemForm[{$idx}][is_tax]", (bool)$item->is_tax, [
                                    'class' => 'form-check-input',
                                    'value' => '1',
                                ]) ?>
                            </td>
                            <td class="text-center align-middle">
                                <?= Html::hiddenInput("PayslipItemForm[{$idx}][sort_order]", $idx, ['data-sort-order' => true]) ?>
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm py-0 px-1"
                                        data-wage-remove-item
                                        title="Удалить">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ошибки строк -->
    <?php if ($model->hasErrors('items')): ?>
        <div class="alert alert-danger">
            <?php foreach ($model->getErrors('items') as $err): ?>
                <div><?= Html::encode($err) ?></div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <!-- Кнопки -->
    <div class="d-flex gap-2">
        <?= Html::submitButton('<i class="bi bi-save me-1"></i>Сохранить', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

<?php ActiveForm::end() ?>
