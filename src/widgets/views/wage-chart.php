<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this  yii\web\View */
/* @var $chartId string     */
/* @var $height int         */
?>
<div class="wage-chart-wrapper" style="position:relative; width:100%;">
    <canvas id="<?= htmlspecialchars($chartId) ?>"
            style="display:block; width:100%; max-width:100%;"
            height="<?= (int)$height ?>">
    </canvas>
</div>
