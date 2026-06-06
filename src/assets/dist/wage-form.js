/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * WageFormManager — compiled from wage-form.ts
 * Сборка: esbuild src/wage-form.ts --bundle --target=es2020 --outfile=dist/wage-form.js
 */
"use strict";
var WageFormManager = (function () {
    function WageFormManager() {
        this.form        = null;
        this.tbody       = null;
        this.rowTemplate = "";
        this.rowCounter  = 0;
    }

    WageFormManager.prototype.init = function (formId) {
        this.form = document.getElementById(formId);
        if (!this.form) { console.warn("[WageForm] Form #" + formId + " not found."); return; }

        this.tbody = this.form.querySelector("[data-wage-items]");
        if (!this.tbody) { console.warn("[WageForm] [data-wage-items] not found."); return; }

        var _this  = this;
        var addBtn = this.form.querySelector("[data-wage-add-item]");
        if (addBtn) {
            this.rowTemplate = addBtn.getAttribute("data-row-template") || "";
            addBtn.addEventListener("click", function (e) { e.preventDefault(); _this.addItem(); });
        }

        this.rowCounter = this.tbody.querySelectorAll("tr[data-wage-row]").length;

        this.tbody.addEventListener("click", function (e) {
            var btn = e.target.closest("button[data-wage-remove-item]");
            if (btn) { e.preventDefault(); _this.removeItem(btn); }
        });
    };

    WageFormManager.prototype.addItem = function () {
        if (!this.tbody || !this.rowTemplate) return;
        var idx = this.rowCounter++;
        var html = this.rowTemplate.split("__IDX__").join(String(idx));
        var tr   = document.createElement("tr");
        tr.setAttribute("data-wage-row", String(idx));
        tr.innerHTML = html;
        this.tbody.appendChild(tr);
        this.updateSortOrders();
        var firstInput = tr.querySelector("input[type=text]");
        if (firstInput) firstInput.focus();
    };

    WageFormManager.prototype.removeItem = function (button) {
        var row = button.closest("tr[data-wage-row]");
        if (!row || !this.tbody) return;
        var rows = this.tbody.querySelectorAll("tr[data-wage-row]");
        if (rows.length <= 1) {
            alert("\u041A\u0432\u0438\u0442\u043E\u043A \u0434\u043E\u043B\u0436\u0435\u043D \u0441\u043E\u0434\u0435\u0440\u0436\u0430\u0442\u044C \u0445\u043E\u0442\u044F \u0431\u044B \u043E\u0434\u043D\u0443 \u0441\u0442\u0440\u043E\u043A\u0443.");
            return;
        }
        row.remove();
        this.updateSortOrders();
    };

    WageFormManager.prototype.updateSortOrders = function () {
        if (!this.tbody) return;
        var rows = this.tbody.querySelectorAll("tr[data-wage-row]");
        rows.forEach(function (row, index) {
            var sortInput = row.querySelector("input[data-sort-order]");
            if (sortInput) sortInput.value = String(index);
        });
    };

    return WageFormManager;
}());

window.WageForm = new WageFormManager();

document.addEventListener("DOMContentLoaded", function () {
    var form = document.querySelector("[data-wage-form]");
    if (form && form.id) window.WageForm.init(form.id);
});
