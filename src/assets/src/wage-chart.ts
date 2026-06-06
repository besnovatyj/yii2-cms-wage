/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * WageBarChart — лёгкий bar chart на Canvas API без сторонних зависимостей.
 * Отображает помесячную статистику зарплат (net + tax стек).
 *
 * Сборка: esbuild wage-chart.ts --bundle --target=es2020 --outfile=../dist/wage-chart.js
 */

"use strict";

// ===== Интерфейсы =====

interface ChartDataPoint {
    /** Метка периода на оси X (напр. «Мар\n2025») */
    label: string;
    /** Чистая сумма (без налогов) */
    netAmount: number;
    /** Сумма налогов */
    taxAmount: number;
}

interface WageChartConfig {
    /** ID элемента canvas */
    canvasId: string;
    /** Данные для отображения */
    data: ChartDataPoint[];
    /** Символ валюты (по умолчанию ₽) */
    currencySymbol?: string;
    /** Цвет столбца net (по умолчанию #4caf50) */
    barNetColor?: string;
    /** Цвет столбца tax (по умолчанию #f44336) */
    barTaxColor?: string;
    /** Цвет фона canvas (по умолчанию #ffffff) */
    backgroundColor?: string;
    /** Цвет текста и осей (по умолчанию #333333) */
    textColor?: string;
    /** Цвет линий сетки (по умолчанию #e8e8e8) */
    gridColor?: string;
    /** Locale для форматирования чисел (по умолчанию ru-RU) */
    locale?: string;
    /** Высота canvas в px (по умолчанию 400) */
    height?: number;
}

// ===== Реализация =====

class WageBarChart {
    private readonly canvas: HTMLCanvasElement;
    private readonly ctx: CanvasRenderingContext2D;
    private readonly data: ChartDataPoint[];
    private readonly cfg: Required<WageChartConfig>;

    // Отступы холста
    private readonly PAD_TOP: number    = 30;
    private readonly PAD_RIGHT: number  = 20;
    private readonly PAD_BOTTOM: number = 70;
    private readonly PAD_LEFT: number   = 90;

    /** Индекс столбца под курсором (-1 = нет) */
    private hoverIndex: number = -1;

    constructor(config: WageChartConfig) {
        const canvas = document.getElementById(config.canvasId) as HTMLCanvasElement | null;
        if (!canvas) {
            throw new Error(`[WageChart] Элемент canvas не найден: #${config.canvasId}`);
        }

        const ctx = canvas.getContext("2d");
        if (!ctx) {
            throw new Error("[WageChart] Не удалось получить 2d-контекст.");
        }

        this.canvas = canvas;
        this.ctx    = ctx;
        this.data   = config.data;

        this.cfg = {
            canvasId:        config.canvasId,
            data:            config.data,
            currencySymbol:  config.currencySymbol  ?? "₽",
            barNetColor:     config.barNetColor     ?? "#4caf50",
            barTaxColor:     config.barTaxColor     ?? "#f44336",
            backgroundColor: config.backgroundColor ?? "#ffffff",
            textColor:       config.textColor       ?? "#333333",
            gridColor:       config.gridColor       ?? "#e8e8e8",
            locale:          config.locale          ?? "ru-RU",
            height:          config.height          ?? 400,
        };

        this.canvas.height = this.cfg.height;
        this.setupResizeObserver();
        this.fitWidth();
        this.render();
        this.bindMouseEvents();
    }

    // ===== Геттеры =====

    private get chartW(): number {
        return this.canvas.width - this.PAD_LEFT - this.PAD_RIGHT;
    }

    private get chartH(): number {
        return this.canvas.height - this.PAD_TOP - this.PAD_BOTTOM;
    }

    private get maxValue(): number {
        if (this.data.length === 0) return 1;
        const max = Math.max(...this.data.map(d => d.netAmount + d.taxAmount));
        return max > 0 ? max * 1.12 : 1;
    }

    private get barWidth(): number {
        if (this.data.length === 0) return 30;
        return Math.max(10, (this.chartW / this.data.length) * 0.55);
    }

    private get barGap(): number {
        if (this.data.length === 0) return 10;
        return this.chartW / this.data.length;
    }

    // ===== Вспомогательные методы =====

    private formatAmount(value: number): string {
        return new Intl.NumberFormat(this.cfg.locale, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value) + " " + this.cfg.currencySymbol;
    }

    private barX(index: number): number {
        return this.PAD_LEFT + index * this.barGap + (this.barGap - this.barWidth) / 2;
    }

    private valueToY(value: number): number {
        return this.PAD_TOP + this.chartH - (value / this.maxValue) * this.chartH;
    }

    private roundRect(x: number, y: number, w: number, h: number, r: number): void {
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
    }

    // ===== Отрисовка =====

    private render(): void {
        const { ctx, canvas } = this;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Фон
        ctx.fillStyle = this.cfg.backgroundColor;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        if (this.data.length === 0) {
            this.drawEmpty();
            return;
        }

        this.drawGrid();
        this.drawBars();
        this.drawXLabels();
        this.drawLegend();

        if (this.hoverIndex >= 0) {
            this.drawTooltip(this.hoverIndex);
        }
    }

    private drawEmpty(): void {
        const { ctx, canvas } = this;
        ctx.fillStyle = this.cfg.textColor;
        ctx.font      = "16px sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("Нет данных за выбранный период", canvas.width / 2, canvas.height / 2);
    }

    private drawGrid(): void {
        const { ctx } = this;
        const gridCount = 5;

        ctx.strokeStyle = this.cfg.gridColor;
        ctx.lineWidth   = 1;
        ctx.setLineDash([4, 4]);

        for (let i = 0; i <= gridCount; i++) {
            const ratio = i / gridCount;
            const y     = this.PAD_TOP + ratio * this.chartH;
            const value = this.maxValue * (1 - ratio);

            ctx.beginPath();
            ctx.moveTo(this.PAD_LEFT, y);
            ctx.lineTo(this.PAD_LEFT + this.chartW, y);
            ctx.stroke();

            ctx.fillStyle  = this.cfg.textColor;
            ctx.font       = "11px sans-serif";
            ctx.textAlign  = "right";
            ctx.textBaseline = "middle";
            ctx.fillText(this.formatAmount(value), this.PAD_LEFT - 8, y);
        }

        ctx.setLineDash([]);

        // Вертикальная линия Y-оси
        ctx.strokeStyle = this.cfg.textColor;
        ctx.lineWidth   = 1;
        ctx.beginPath();
        ctx.moveTo(this.PAD_LEFT, this.PAD_TOP);
        ctx.lineTo(this.PAD_LEFT, this.PAD_TOP + this.chartH);
        ctx.stroke();

        // Горизонтальная линия X-оси
        ctx.beginPath();
        ctx.moveTo(this.PAD_LEFT, this.PAD_TOP + this.chartH);
        ctx.lineTo(this.PAD_LEFT + this.chartW, this.PAD_TOP + this.chartH);
        ctx.stroke();
    }

    private drawBars(): void {
        const { ctx } = this;
        const baseY = this.PAD_TOP + this.chartH;

        this.data.forEach((point, i) => {
            const x       = this.barX(i);
            const netH    = (point.netAmount / this.maxValue) * this.chartH;
            const taxH    = (point.taxAmount / this.maxValue) * this.chartH;
            const totalH  = netH + taxH;
            const isHover = i === this.hoverIndex;

            // Подсветка при hover
            if (isHover) {
                ctx.fillStyle = "rgba(0,0,0,0.05)";
                ctx.fillRect(x - 4, this.PAD_TOP, this.barWidth + 8, this.chartH);
            }

            // Net-часть (нижняя, зелёная)
            if (netH > 0) {
                ctx.fillStyle = this.cfg.barNetColor;
                this.roundRect(x, baseY - totalH, this.barWidth, netH, 0);
                ctx.fill();
            }

            // Tax-часть (верхняя, красная)
            if (taxH > 0) {
                ctx.fillStyle = this.cfg.barTaxColor;
                this.roundRect(x, baseY - taxH, this.barWidth, taxH, 4);
                ctx.fill();
            }

            // Если только net, скруглить верх
            if (netH > 0 && taxH === 0) {
                ctx.fillStyle = this.cfg.barNetColor;
                this.roundRect(x, baseY - netH, this.barWidth, netH, 4);
                ctx.fill();
            }
        });
    }

    private drawXLabels(): void {
        const { ctx } = this;
        const baseY   = this.PAD_TOP + this.chartH;

        ctx.fillStyle    = this.cfg.textColor;
        ctx.font         = "11px sans-serif";
        ctx.textAlign    = "center";
        ctx.textBaseline = "top";

        this.data.forEach((point, i) => {
            const cx     = this.barX(i) + this.barWidth / 2;
            const lines  = point.label.split("\n");
            lines.forEach((line, li) => {
                ctx.fillText(line, cx, baseY + 8 + li * 14);
            });
        });
    }

    private drawLegend(): void {
        const { ctx, canvas } = this;
        const legendY         = canvas.height - 18;
        const sqSize          = 12;
        let x                 = this.PAD_LEFT;

        // Net
        ctx.fillStyle = this.cfg.barNetColor;
        ctx.fillRect(x, legendY, sqSize, sqSize);
        ctx.fillStyle    = this.cfg.textColor;
        ctx.font         = "12px sans-serif";
        ctx.textAlign    = "left";
        ctx.textBaseline = "middle";
        ctx.fillText("Чистая сумма", x + sqSize + 5, legendY + sqSize / 2);
        x += 130;

        // Tax
        ctx.fillStyle = this.cfg.barTaxColor;
        ctx.fillRect(x, legendY, sqSize, sqSize);
        ctx.fillStyle = this.cfg.textColor;
        ctx.fillText("Налоги", x + sqSize + 5, legendY + sqSize / 2);
    }

    private drawTooltip(index: number): void {
        if (index < 0 || index >= this.data.length) return;

        const { ctx, canvas } = this;
        const point           = this.data[index];
        const total           = point.netAmount + point.taxAmount;

        const lines: string[] = [
            point.label.replace("\n", " "),
            `Итого:  ${this.formatAmount(total)}`,
            `Налог:  ${this.formatAmount(point.taxAmount)}`,
            `Net:    ${this.formatAmount(point.netAmount)}`,
        ];

        const padding    = 10;
        const lineHeight = 20;
        const boxW       = 210;
        const boxH       = lines.length * lineHeight + padding * 2;

        const cx = this.barX(index) + this.barWidth / 2;
        let tx   = cx - boxW / 2;
        let ty   = this.PAD_TOP + 5;

        // Не выходим за правую границу
        tx = Math.max(this.PAD_LEFT, Math.min(tx, canvas.width - boxW - 5));

        // Тень
        ctx.shadowColor   = "rgba(0,0,0,0.2)";
        ctx.shadowBlur    = 8;
        ctx.shadowOffsetY = 3;

        ctx.fillStyle = "rgba(30,30,30,0.88)";
        this.roundRect(tx, ty, boxW, boxH, 6);
        ctx.fill();

        ctx.shadowColor = "transparent";
        ctx.shadowBlur  = 0;

        ctx.fillStyle    = "#ffffff";
        ctx.textBaseline = "top";
        ctx.textAlign    = "left";

        lines.forEach((line, i) => {
            ctx.font = i === 0 ? "bold 12px sans-serif" : "12px sans-serif";
            ctx.fillText(line, tx + padding, ty + padding + i * lineHeight);
        });
    }

    // ===== Обработка событий =====

    private bindMouseEvents(): void {
        this.canvas.addEventListener("mousemove", (e: MouseEvent) => {
            const rect  = this.canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            let found   = -1;

            this.data.forEach((_, i) => {
                const bx = this.barX(i);
                if (mouseX >= bx && mouseX <= bx + this.barWidth) {
                    found = i;
                }
            });

            if (found !== this.hoverIndex) {
                this.hoverIndex = found;
                this.render();
            }
        });

        this.canvas.addEventListener("mouseleave", () => {
            if (this.hoverIndex !== -1) {
                this.hoverIndex = -1;
                this.render();
            }
        });
    }

    private setupResizeObserver(): void {
        const observer = new ResizeObserver(() => {
            this.fitWidth();
            this.render();
        });
        observer.observe(this.canvas.parentElement ?? this.canvas);
    }

    private fitWidth(): void {
        const parent = this.canvas.parentElement;
        if (parent) {
            this.canvas.width = parent.clientWidth || 600;
        }
    }
}

// ===== Публичный API =====

declare global {
    interface Window {
        /** Фабрика создания экземпляра графика */
        createWageChart: (config: WageChartConfig) => WageBarChart;
    }
}

window.createWageChart = (config: WageChartConfig): WageBarChart => {
    return new WageBarChart(config);
};
