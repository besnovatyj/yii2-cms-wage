/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * WageBarChart — compiled from wage-chart.ts
 * Сборка: esbuild src/wage-chart.ts --bundle --target=es2020 --outfile=dist/wage-chart.js
 */
"use strict";
var WageBarChart = (function () {
    function WageBarChart(config) {
        this.hoverIndex = -1;
        this.PAD_TOP    = 30;
        this.PAD_RIGHT  = 20;
        this.PAD_BOTTOM = 70;
        this.PAD_LEFT   = 90;

        var canvas = document.getElementById(config.canvasId);
        if (!canvas) throw new Error("[WageChart] Canvas not found: #" + config.canvasId);
        var ctx = canvas.getContext("2d");
        if (!ctx) throw new Error("[WageChart] Cannot get 2d context.");

        this.canvas = canvas;
        this.ctx    = ctx;
        this.data   = config.data;
        this.cfg    = {
            canvasId:        config.canvasId,
            data:            config.data,
            currencySymbol:  config.currencySymbol  != null ? config.currencySymbol  : "\u20BD",
            barNetColor:     config.barNetColor     != null ? config.barNetColor     : "#4caf50",
            barTaxColor:     config.barTaxColor     != null ? config.barTaxColor     : "#f44336",
            backgroundColor: config.backgroundColor != null ? config.backgroundColor : "#ffffff",
            textColor:       config.textColor       != null ? config.textColor       : "#333333",
            gridColor:       config.gridColor       != null ? config.gridColor       : "#e8e8e8",
            locale:          config.locale          != null ? config.locale          : "ru-RU",
            height:          config.height          != null ? config.height          : 400,
        };

        this.canvas.height = this.cfg.height;
        this.setupResizeObserver();
        this.fitWidth();
        this.render();
        this.bindMouseEvents();
    }

    Object.defineProperty(WageBarChart.prototype, "chartW", {
        get: function () { return this.canvas.width - this.PAD_LEFT - this.PAD_RIGHT; }
    });
    Object.defineProperty(WageBarChart.prototype, "chartH", {
        get: function () { return this.canvas.height - this.PAD_TOP - this.PAD_BOTTOM; }
    });
    Object.defineProperty(WageBarChart.prototype, "maxValue", {
        get: function () {
            if (this.data.length === 0) return 1;
            var max = Math.max.apply(Math, this.data.map(function (d) { return d.netAmount + d.taxAmount; }));
            return max > 0 ? max * 1.12 : 1;
        }
    });
    Object.defineProperty(WageBarChart.prototype, "barWidth", {
        get: function () {
            if (this.data.length === 0) return 30;
            return Math.max(10, (this.chartW / this.data.length) * 0.55);
        }
    });
    Object.defineProperty(WageBarChart.prototype, "barGap", {
        get: function () {
            if (this.data.length === 0) return 10;
            return this.chartW / this.data.length;
        }
    });

    WageBarChart.prototype.formatAmount = function (value) {
        return new Intl.NumberFormat(this.cfg.locale, { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value) + " " + this.cfg.currencySymbol;
    };

    WageBarChart.prototype.barX = function (index) {
        return this.PAD_LEFT + index * this.barGap + (this.barGap - this.barWidth) / 2;
    };

    WageBarChart.prototype.valueToY = function (value) {
        return this.PAD_TOP + this.chartH - (value / this.maxValue) * this.chartH;
    };

    WageBarChart.prototype.roundRect = function (x, y, w, h, r) {
        if (h <= 0) return;
        r = Math.min(r, h / 2, w / 2);
        this.ctx.beginPath();
        this.ctx.moveTo(x + r, y);
        this.ctx.lineTo(x + w - r, y);
        this.ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        this.ctx.lineTo(x + w, y + h - r);
        this.ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        this.ctx.lineTo(x + r, y + h);
        this.ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        this.ctx.lineTo(x, y + r);
        this.ctx.quadraticCurveTo(x, y, x + r, y);
        this.ctx.closePath();
    };

    WageBarChart.prototype.render = function () {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.ctx.fillStyle = this.cfg.backgroundColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        if (this.data.length === 0) { this.drawEmpty(); return; }
        this.drawGrid();
        this.drawBars();
        this.drawXLabels();
        this.drawLegend();
        if (this.hoverIndex >= 0) this.drawTooltip(this.hoverIndex);
    };

    WageBarChart.prototype.drawEmpty = function () {
        var ctx = this.ctx;
        ctx.fillStyle = this.cfg.textColor;
        ctx.font      = "16px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("\u041D\u0435\u0442 \u0434\u0430\u043D\u043D\u044B\u0445 \u0437\u0430 \u0432\u044B\u0431\u0440\u0430\u043D\u043D\u044B\u0439 \u043F\u0435\u0440\u0438\u043E\u0434",
            this.canvas.width / 2, this.canvas.height / 2);
    };

    WageBarChart.prototype.drawGrid = function () {
        var ctx = this.ctx, gridCount = 5;
        ctx.strokeStyle = this.cfg.gridColor;
        ctx.lineWidth   = 1;
        ctx.setLineDash([4, 4]);
        for (var i = 0; i <= gridCount; i++) {
            var ratio = i / gridCount;
            var y     = this.PAD_TOP + ratio * this.chartH;
            var value = this.maxValue * (1 - ratio);
            ctx.beginPath(); ctx.moveTo(this.PAD_LEFT, y); ctx.lineTo(this.PAD_LEFT + this.chartW, y); ctx.stroke();
            ctx.fillStyle = this.cfg.textColor; ctx.font = "11px sans-serif";
            ctx.textAlign = "right"; ctx.textBaseline = "middle";
            ctx.fillText(this.formatAmount(value), this.PAD_LEFT - 8, y);
        }
        ctx.setLineDash([]);
        ctx.strokeStyle = this.cfg.textColor; ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(this.PAD_LEFT, this.PAD_TOP); ctx.lineTo(this.PAD_LEFT, this.PAD_TOP + this.chartH); ctx.stroke();
        ctx.beginPath(); ctx.moveTo(this.PAD_LEFT, this.PAD_TOP + this.chartH); ctx.lineTo(this.PAD_LEFT + this.chartW, this.PAD_TOP + this.chartH); ctx.stroke();
    };

    WageBarChart.prototype.drawBars = function () {
        var ctx   = this.ctx;
        var baseY = this.PAD_TOP + this.chartH;
        var _this = this;
        this.data.forEach(function (point, i) {
            var x       = _this.barX(i);
            var netH    = (point.netAmount / _this.maxValue) * _this.chartH;
            var taxH    = (point.taxAmount / _this.maxValue) * _this.chartH;
            var totalH  = netH + taxH;
            var isHover = i === _this.hoverIndex;
            if (isHover) { ctx.fillStyle = "rgba(0,0,0,0.05)"; ctx.fillRect(x - 4, _this.PAD_TOP, _this.barWidth + 8, _this.chartH); }
            if (netH > 0 && taxH > 0) {
                ctx.fillStyle = _this.cfg.barNetColor; _this.roundRect(x, baseY - totalH, _this.barWidth, netH, 0); ctx.fill();
                ctx.fillStyle = _this.cfg.barTaxColor; _this.roundRect(x, baseY - taxH, _this.barWidth, taxH, 4); ctx.fill();
            } else if (netH > 0) {
                ctx.fillStyle = _this.cfg.barNetColor; _this.roundRect(x, baseY - netH, _this.barWidth, netH, 4); ctx.fill();
            } else if (taxH > 0) {
                ctx.fillStyle = _this.cfg.barTaxColor; _this.roundRect(x, baseY - taxH, _this.barWidth, taxH, 4); ctx.fill();
            }
        });
    };

    WageBarChart.prototype.drawXLabels = function () {
        var ctx   = this.ctx;
        var baseY = this.PAD_TOP + this.chartH;
        var _this = this;
        ctx.fillStyle = this.cfg.textColor; ctx.font = "11px sans-serif";
        ctx.textAlign = "center"; ctx.textBaseline = "top";
        this.data.forEach(function (point, i) {
            var cx    = _this.barX(i) + _this.barWidth / 2;
            var lines = point.label.split("\n");
            lines.forEach(function (line, li) { ctx.fillText(line, cx, baseY + 8 + li * 14); });
        });
    };

    WageBarChart.prototype.drawLegend = function () {
        var ctx     = this.ctx;
        var legendY = this.canvas.height - 18;
        var sqSize  = 12;
        var x       = this.PAD_LEFT;
        ctx.fillStyle = this.cfg.barNetColor; ctx.fillRect(x, legendY, sqSize, sqSize);
        ctx.fillStyle = this.cfg.textColor; ctx.font = "12px sans-serif"; ctx.textAlign = "left"; ctx.textBaseline = "middle";
        ctx.fillText("\u0427\u0438\u0441\u0442\u0430\u044F \u0441\u0443\u043C\u043C\u0430", x + sqSize + 5, legendY + sqSize / 2);
        x += 130;
        ctx.fillStyle = this.cfg.barTaxColor; ctx.fillRect(x, legendY, sqSize, sqSize);
        ctx.fillStyle = this.cfg.textColor;
        ctx.fillText("\u041D\u0430\u043B\u043E\u0433\u0438", x + sqSize + 5, legendY + sqSize / 2);
    };

    WageBarChart.prototype.drawTooltip = function (index) {
        if (index < 0 || index >= this.data.length) return;
        var ctx   = this.ctx, canvas = this.canvas;
        var point = this.data[index];
        var total = point.netAmount + point.taxAmount;
        var lines = [
            point.label.replace("\n", " "),
            "\u0418\u0442\u043E\u0433\u043E:  " + this.formatAmount(total),
            "\u041D\u0430\u043B\u043E\u0433:  " + this.formatAmount(point.taxAmount),
            "Net:    " + this.formatAmount(point.netAmount),
        ];
        var padding = 10, lineHeight = 20, boxW = 210, boxH = lines.length * lineHeight + padding * 2;
        var cx = this.barX(index) + this.barWidth / 2;
        var tx = Math.max(this.PAD_LEFT, Math.min(cx - boxW / 2, canvas.width - boxW - 5));
        var ty = this.PAD_TOP + 5;
        ctx.shadowColor = "rgba(0,0,0,0.2)"; ctx.shadowBlur = 8; ctx.shadowOffsetY = 3;
        ctx.fillStyle   = "rgba(30,30,30,0.88)"; this.roundRect(tx, ty, boxW, boxH, 6); ctx.fill();
        ctx.shadowColor = "transparent"; ctx.shadowBlur = 0;
        ctx.fillStyle = "#ffffff"; ctx.textBaseline = "top"; ctx.textAlign = "left";
        lines.forEach(function (line, i) {
            ctx.font = i === 0 ? "bold 12px sans-serif" : "12px sans-serif";
            ctx.fillText(line, tx + padding, ty + padding + i * lineHeight);
        });
    };

    WageBarChart.prototype.bindMouseEvents = function () {
        var _this = this;
        this.canvas.addEventListener("mousemove", function (e) {
            var rect   = _this.canvas.getBoundingClientRect();
            var mouseX = e.clientX - rect.left;
            var found  = -1;
            _this.data.forEach(function (_, i) {
                var bx = _this.barX(i);
                if (mouseX >= bx && mouseX <= bx + _this.barWidth) found = i;
            });
            if (found !== _this.hoverIndex) { _this.hoverIndex = found; _this.render(); }
        });
        this.canvas.addEventListener("mouseleave", function () {
            if (_this.hoverIndex !== -1) { _this.hoverIndex = -1; _this.render(); }
        });
    };

    WageBarChart.prototype.setupResizeObserver = function () {
        var _this   = this;
        var observer = new ResizeObserver(function () { _this.fitWidth(); _this.render(); });
        observer.observe(this.canvas.parentElement || this.canvas);
    };

    WageBarChart.prototype.fitWidth = function () {
        var parent = this.canvas.parentElement;
        if (parent) this.canvas.width = parent.clientWidth || 600;
    };

    return WageBarChart;
}());

window.createWageChart = function (config) { return new WageBarChart(config); };
