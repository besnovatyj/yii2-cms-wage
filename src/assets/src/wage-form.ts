/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Менеджер динамической формы зарплатного квитка.
 * Управляет добавлением и удалением строк (статей выплат) в форме.
 *
 * Сборка: esbuild wage-form.ts --bundle --target=es2020 --outfile=../dist/wage-form.js
 */

"use strict";

// ===== Интерфейсы =====

interface PayslipFormManager {
    /** Добавляет новую строку в таблицу строк квитка */
    addItem(): void;
    /** Удаляет строку по кнопке */
    removeItem(button: HTMLButtonElement): void;
    /** Инициализирует менеджер формы по ID формы */
    init(formId: string): void;
}

// ===== Реализация =====

class WageFormManager implements PayslipFormManager {
    private form: HTMLFormElement | null = null;
    private tbody: HTMLTableSectionElement | null = null;

    /** HTML-шаблон одной строки (берётся из data-row-template на кнопке добавления) */
    private rowTemplate: string = "";

    /** Счётчик для уникальных индексов строк */
    private rowCounter: number = 0;

    public init(formId: string): void {
        this.form  = document.getElementById(formId) as HTMLFormElement | null;
        if (!this.form) {
            console.warn(`[WageForm] Форма #${formId} не найдена.`);
            return;
        }

        this.tbody = this.form.querySelector<HTMLTableSectionElement>("[data-wage-items]");
        if (!this.tbody) {
            console.warn("[WageForm] Элемент [data-wage-items] не найден.");
            return;
        }

        const addBtn = this.form.querySelector<HTMLButtonElement>("[data-wage-add-item]");
        if (addBtn) {
            this.rowTemplate = addBtn.getAttribute("data-row-template") ?? "";
            addBtn.addEventListener("click", (e: Event) => {
                e.preventDefault();
                this.addItem();
            });
        }

        // Инициализируем счётчик по числу уже имеющихся строк
        this.rowCounter = this.tbody.querySelectorAll("tr[data-wage-row]").length;

        // Делегированный обработчик кнопок удаления
        this.tbody.addEventListener("click", (e: Event) => {
            const target = e.target as HTMLElement;
            const btn = target.closest<HTMLButtonElement>("button[data-wage-remove-item]");
            if (btn) {
                e.preventDefault();
                this.removeItem(btn);
            }
        });
    }

    public addItem(): void {
        if (!this.tbody || !this.rowTemplate) return;

        const idx  = this.rowCounter++;
        const html = this.rowTemplate.replaceAll("__IDX__", String(idx));

        const tr   = document.createElement("tr");
        tr.setAttribute("data-wage-row", String(idx));
        tr.innerHTML = html;

        this.tbody.appendChild(tr);
        this.updateSortOrders();

        // Фокус на первое поле новой строки
        const firstInput = tr.querySelector<HTMLInputElement>("input[type=text]");
        firstInput?.focus();
    }

    public removeItem(button: HTMLButtonElement): void {
        const row = button.closest<HTMLTableRowElement>("tr[data-wage-row]");
        if (!row || !this.tbody) return;

        // Нельзя удалить последнюю строку
        const rows = this.tbody.querySelectorAll("tr[data-wage-row]");
        if (rows.length <= 1) {
            alert("Квиток должен содержать хотя бы одну строку.");
            return;
        }

        row.remove();
        this.updateSortOrders();
    }

    /**
     * Обновляет скрытые поля sort_order в строках после добавления/удаления.
     */
    private updateSortOrders(): void {
        if (!this.tbody) return;

        const rows = this.tbody.querySelectorAll<HTMLTableRowElement>("tr[data-wage-row]");
        rows.forEach((row, index) => {
            const sortInput = row.querySelector<HTMLInputElement>("input[data-sort-order]");
            if (sortInput) {
                sortInput.value = String(index);
            }
        });
    }
}

// ===== Публичный API =====

declare global {
    interface Window {
        /** Singleton менеджера формы квитка */
        WageForm: WageFormManager;
    }
}

window.WageForm = new WageFormManager();

// Автоинициализация при готовности DOM
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector<HTMLFormElement>("[data-wage-form]");
    if (form?.id) {
        window.WageForm.init(form.id);
    }
});
