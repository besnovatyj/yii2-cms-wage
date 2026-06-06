<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\entities\queries;

use yii\db\ActiveQuery;

/**
 * ActiveQuery для сущности PayslipItem.
 */
class PayslipItemQuery extends ActiveQuery
{
    /**
     * Только налоговые строки.
     */
    public function onlyTaxes(): static
    {
        return $this->andWhere(['is_tax' => true]);
    }

    /**
     * Только не-налоговые строки (чистые выплаты).
     */
    public function onlyNet(): static
    {
        return $this->andWhere(['is_tax' => false]);
    }
}
