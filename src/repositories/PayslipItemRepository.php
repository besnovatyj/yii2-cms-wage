<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\repositories;

use Besnovatyj\Wage\entities\PayslipItem;
use RuntimeException;

/**
 * Репозиторий строк квитка.
 */
class PayslipItemRepository
{
    /**
     * Сохраняет строку квитка.
     *
     * @throws RuntimeException при ошибке сохранения
     */
    public function save(PayslipItem $item): void
    {
        if (!$item->save()) {
            throw new RuntimeException('Ошибка сохранения строки квитка: ' . json_encode($item->getErrors()));
        }
    }

    /**
     * Удаляет все строки указанного квитка.
     */
    public function removeAllByPayslipId(int $payslipId): void
    {
        PayslipItem::deleteAll(['payslip_id' => $payslipId]);
    }
}
