<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Wage\forms\backend\PayslipForm;

/* @var $this  yii\web\View */
/* @var $model PayslipForm  */

$this->title                   = 'Создать квиток';
$this->params['breadcrumbs'][] = ['label' => 'Квитки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wage-payslip-create">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
