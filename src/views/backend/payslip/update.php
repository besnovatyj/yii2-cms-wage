<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\forms\backend\PayslipForm;
use yii\helpers\Html;

/* @var $this    yii\web\View */
/* @var $model   PayslipForm  */
/* @var $payslip Payslip      */

$this->title                   = 'Редактировать: ' . $payslip->getPeriodLabel();
$this->params['breadcrumbs'][] = ['label' => 'Квитки', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $payslip->getPeriodLabel(), 'url' => ['view', 'id' => $payslip->id]];
$this->params['breadcrumbs'][] = 'Редактировать';
?>
<div class="wage-payslip-update">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
