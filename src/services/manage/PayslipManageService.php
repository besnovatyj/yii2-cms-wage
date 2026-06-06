<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\services\manage;

use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\entities\PayslipItem;
use Besnovatyj\Wage\forms\backend\PayslipForm;
use Besnovatyj\Wage\forms\backend\PayslipItemForm;
use Besnovatyj\Wage\repositories\PayslipItemRepository;
use Besnovatyj\Wage\repositories\PayslipRepository;
use DomainException;
use Throwable;
use Yii;

/**
 * Сервис управления жизненным циклом зарплатного квитка.
 * Обеспечивает транзакционное создание, обновление и удаление квитков со строками.
 */
class PayslipManageService
{
    public function __construct(
        private readonly PayslipRepository     $payslips,
        private readonly PayslipItemRepository $items,
    ) {
    }

    /**
     * Создаёт новый квиток вместе со строками.
     *
     * @throws DomainException если квиток за данный период уже существует
     * @throws Throwable
     */
    public function create(PayslipForm $form): Payslip
    {
        if ($this->payslips->existsByPeriod($form->year, $form->month)) {
            throw new DomainException(
                "Квиток за {$form->month}/{$form->year} уже существует."
            );
        }

        $payslip = Payslip::create(
            $form->year,
            $form->month,
            $form->note ?: null,
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->payslips->save($payslip);
            $this->saveItems($payslip->id, $form->items);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $payslip;
    }

    /**
     * Редактирует существующий квиток и пересохраняет его строки.
     *
     * @throws DomainException если квиток за данный период уже занят другим квитком
     * @throws Throwable
     */
    public function edit(Payslip $payslip, PayslipForm $form): void
    {
        if ($this->payslips->existsByPeriod($form->year, $form->month, $payslip->id)) {
            throw new DomainException(
                "Квиток за {$form->month}/{$form->year} уже существует."
            );
        }

        $payslip->edit(
            $form->year,
            $form->month,
            $form->note ?: null,
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->payslips->save($payslip);

            // Пересохраняем строки: удаляем старые, создаём новые
            $this->items->removeAllByPayslipId($payslip->id);
            $this->saveItems($payslip->id, $form->items);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Удаляет квиток вместе со всеми строками.
     *
     * @throws Throwable
     */
    public function remove(int $id): void
    {
        $payslip = $this->payslips->get($id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->items->removeAllByPayslipId($payslip->id);
            $this->payslips->remove($payslip);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // ===== Приватные методы =====

    /**
     * Создаёт строки квитка по данным форм.
     *
     * @param PayslipItemForm[] $itemForms
     */
    private function saveItems(int $payslipId, array $itemForms): void
    {
        foreach ($itemForms as $index => $itemForm) {
            $item = PayslipItem::create(
                $payslipId,
                $itemForm->title,
                $itemForm->getAmountFloat(),
                (bool)$itemForm->is_tax,
                $index,
            );
            $this->items->save($item);
        }
    }
}
