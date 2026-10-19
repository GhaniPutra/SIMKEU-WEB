const dashboardElement = document.getElementById('dashboard-data');
const dashboardAttributes = dashboardElement?.dataset;
const parseDashboardData = (value) => {
    try {
        return JSON.parse(value || '[]');
    } catch {
        return [];
    }
};

const dashboardData = dashboardAttributes ? {
    period: dashboardAttributes.period,
    periodLabel: dashboardAttributes.periodLabel,
    allPeriodMonthCount: Number(dashboardAttributes.allPeriodMonthCount),
    trendLabels: parseDashboardData(dashboardAttributes.trendLabels),
    revenueData: parseDashboardData(dashboardAttributes.revenueData).map(Number),
    expenseData: parseDashboardData(dashboardAttributes.expenseData).map(Number),
    expenseCategoryLabels: parseDashboardData(dashboardAttributes.expenseCategoryLabels),
    expenseCategoryData: parseDashboardData(dashboardAttributes.expenseCategoryData).map(Number),
    expenseCategoryColors: parseDashboardData(dashboardAttributes.expenseCategoryColors)
} : {
    period: 'month',
    periodLabel: 'Bulan Ini',
    allPeriodMonthCount: 0,
    trendLabels: [],
    revenueData: [],
    expenseData: [],
    expenseCategoryLabels: [],
    expenseCategoryData: [],
    expenseCategoryColors: []
};

function formatRupiah(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

function chartEmptyState(canvas, message) {
    if (!canvas) return;
    canvas.insertAdjacentHTML('afterend', `<p class="chart-empty-state">${message}</p>`);
    canvas.remove();
}

const FINANCIAL_BAR_GAP_PX = 3;
const DASHBOARD_REVENUE_COLOR = getComputedStyle(document.documentElement)
    .getPropertyValue('--green-400').trim() || '#88d66c';
const FINANCIAL_REVENUE_COLOR = getComputedStyle(document.documentElement)
    .getPropertyValue('--blue-400').trim() || '#65b1f5';
const DASHBOARD_EXPENSE_COLOR = getComputedStyle(document.documentElement)
    .getPropertyValue('--red-400').trim() || '#ff5e73';

function getFinancialBarCategoryPercentage(labelCount) {
    if (labelCount <= 1) return 0.10;
    if (labelCount <= 3) return 0.20;
    if (labelCount <= 7) return 0.30;
    if (labelCount <= 14) return 0.40;
    return 0.60;
}

function isFinancialZoomGesture(event) {
    if (event.ctrlKey) return true;

    // deltaMode describes units, not hardware. Line units are a useful
    // best-effort signal for discrete mouse wheels (including Firefox); page
    // units and unmodified pixel events are ambiguous, so leave them to the
    // browser unless it also exposes a legacy wheel notch.
    if (event.deltaMode === 1) return true;

    const legacyWheelDelta = Number(event.wheelDeltaY || event.wheelDelta || 0);
    return legacyWheelDelta !== 0 && Math.abs(legacyWheelDelta) % 120 === 0;
}

function attachFinancialBarZoom(chart, canvas) {
    const lastIndex = chart.data.labels.length - 1;
    if (lastIndex < 1) return;

    let visibleRange = { min: 0, max: lastIndex };
    let previousTouchDistance = null;
    let dragState = null;
    let pendingTrackpadPan = 0;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
    const visibleCount = () => visibleRange.max - visibleRange.min + 1;
    const isZoomed = () => visibleCount() < lastIndex + 1;
    const updateCursor = () => {
        if (!canvas.style) return;
        canvas.style.cursor = dragState ? 'grabbing' : (isZoomed() ? 'grab' : '');
    };

    const getAnchor = (clientX) => {
        const bounds = canvas.getBoundingClientRect();
        const pointerX = clamp(clientX - bounds.left, chart.chartArea.left, chart.chartArea.right);
        const value = chart.scales.x.getValueForPixel(pointerX);

        return Number.isFinite(value)
            ? clamp(Math.round(value), visibleRange.min, visibleRange.max)
            : (visibleRange.min + visibleRange.max) / 2;
    };

    const setHorizontalRange = (min) => {
        const count = visibleCount();
        const nextMin = clamp(Math.round(min), 0, lastIndex + 1 - count);
        if (nextMin === visibleRange.min) return false;

        visibleRange = { min: nextMin, max: nextMin + count - 1 };
        chart.options.scales.x.min = visibleRange.min;
        chart.options.scales.x.max = visibleRange.max;
        chart.update('none');
        updateCursor();

        return true;
    };

    const panWithTrackpad = (deltaX, deltaMode) => {
        if (!isZoomed() || deltaMode === 2) {
            pendingTrackpadPan = 0;
            return false;
        }

        const categoryWidth = chart.chartArea.width / visibleCount();
        if (!(categoryWidth > 0)) return false;

        pendingTrackpadPan += deltaX * (deltaMode === 1 ? 40 : 1);
        const categoryShift = Math.trunc(pendingTrackpadPan / categoryWidth);
        if (categoryShift === 0) return false;

        const changed = setHorizontalRange(visibleRange.min + categoryShift);
        if (changed) {
            pendingTrackpadPan -= categoryShift * categoryWidth;
        } else {
            pendingTrackpadPan = 0;
        }

        return changed;
    };

    const updateVerticalRange = () => {
        const firstIndex = Math.max(0, visibleRange.min);
        const lastVisibleIndex = Math.min(lastIndex, visibleRange.max);
        const visibleMaximum = chart.data.datasets.reduce((maximum, dataset) => {
            const datasetMaximum = dataset.data
                .slice(firstIndex, lastVisibleIndex + 1)
                .reduce((currentMaximum, value) => Math.max(currentMaximum, Number(value) || 0), 0);

            return Math.max(maximum, datasetMaximum);
        }, 0);

        if (visibleRange.min === 0 && visibleRange.max === lastIndex) {
            delete chart.options.scales.y.max;
        } else {
            chart.options.scales.y.max = visibleMaximum > 0 ? visibleMaximum * 1.12 : 1;
        }
    };

    const zoom = (factor, anchor) => {
        const currentCount = visibleRange.max - visibleRange.min + 1;
        const nextCount = Math.round(clamp(currentCount * factor, 1, lastIndex + 1));
        if (nextCount === currentCount) return false;

        const anchorIndex = clamp(Math.round(anchor), visibleRange.min, visibleRange.max);
        const anchorRatio = currentCount > 1
            ? (anchorIndex - visibleRange.min) / (currentCount - 1)
            : 0.5;
        const min = clamp(
            Math.round(anchorIndex - anchorRatio * (nextCount - 1)),
            0,
            lastIndex + 1 - nextCount
        );
        const max = min + nextCount - 1;

        visibleRange = { min, max };
        chart.options.scales.x.min = min;
        chart.options.scales.x.max = max;
        updateVerticalRange();
        chart.update('none');
        updateCursor();

        return true;
    };

    const reset = () => {
        visibleRange = { min: 0, max: lastIndex };
        chart.options.scales.x.min = 0;
        chart.options.scales.x.max = lastIndex;
        delete chart.options.scales.y.max;
        chart.update('none');
        pendingTrackpadPan = 0;
        updateCursor();
    };

    canvas.addEventListener('wheel', (event) => {
        const horizontalGesture = !event.ctrlKey
            && isZoomed()
            && Math.abs(event.deltaX) > Math.abs(event.deltaY)
            && event.deltaX !== 0;

        if (horizontalGesture) {
            const changed = panWithTrackpad(event.deltaX, event.deltaMode);
            if (changed || pendingTrackpadPan !== 0) event.preventDefault();
            return;
        }

        pendingTrackpadPan = 0;
        if (!isFinancialZoomGesture(event)) return;
        if (event.deltaY === 0) {
            if (event.ctrlKey) event.preventDefault();
            return;
        }

        // Unmodified pixel/page scroll events are left to the page. Ctrl+wheel
        // identifies pinch zoom; line units and 120-unit notches are best-effort
        // physical mouse-wheel signals where browsers expose those signals.
        const unitScale = event.deltaMode === 1 ? 40 : 1;
        const delta = clamp(event.deltaY * unitScale, -240, 240);
        const changed = zoom(Math.exp(delta * 0.0015), getAnchor(event.clientX));
        if (changed || event.ctrlKey) event.preventDefault();
    }, { passive: false });

    canvas.addEventListener('pointerdown', (event) => {
        if (!isZoomed() || event.pointerType !== 'mouse' || event.button !== 0) return;

        const categoryWidth = chart.chartArea.width / visibleCount();
        if (!(categoryWidth > 0)) return;

        dragState = {
            pointerId: event.pointerId,
            startX: event.clientX,
            startMin: visibleRange.min,
            categoryWidth
        };
        pendingTrackpadPan = 0;
        if (typeof canvas.setPointerCapture === 'function') {
            canvas.setPointerCapture(event.pointerId);
        }
        updateCursor();
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!dragState || event.pointerId !== dragState.pointerId) return;

        const categoryShift = Math.round((dragState.startX - event.clientX) / dragState.categoryWidth);
        setHorizontalRange(dragState.startMin + categoryShift);
        if (event.cancelable) event.preventDefault();
    }, { passive: false });

    const stopDrag = (event) => {
        if (!dragState || (event && event.pointerId !== dragState.pointerId)) return;

        dragState = null;
        pendingTrackpadPan = 0;
        updateCursor();
    };

    canvas.addEventListener('pointerup', stopDrag);
    canvas.addEventListener('pointercancel', stopDrag);
    canvas.addEventListener('lostpointercapture', stopDrag);

    canvas.addEventListener('dblclick', (event) => {
        if (visibleRange.min === 0 && visibleRange.max === lastIndex) return;

        event.preventDefault();
        reset();
    });

    canvas.addEventListener('touchstart', (event) => {
        if (event.touches.length === 2) {
            const [first, second] = event.touches;
            previousTouchDistance = Math.hypot(second.clientX - first.clientX, second.clientY - first.clientY);
        }
    }, { passive: true });

    canvas.addEventListener('touchmove', (event) => {
        if (event.touches.length !== 2 || !previousTouchDistance) return;

        const [first, second] = event.touches;
        const distance = Math.hypot(second.clientX - first.clientX, second.clientY - first.clientY);
        const midpointX = (first.clientX + second.clientX) / 2;
        zoom(previousTouchDistance / distance, getAnchor(midpointX));
        previousTouchDistance = distance;
        event.preventDefault();
    }, { passive: false });

    const clearTouchZoom = () => {
        previousTouchDistance = null;
    };
    canvas.addEventListener('touchend', clearTouchZoom, { passive: true });
    canvas.addEventListener('touchcancel', clearTouchZoom, { passive: true });
}

function initExpenseCompositionChart() {
    const canvas = document.getElementById('expenseCompositionChart');
    if (!canvas) return;

    const { expenseCategoryLabels, expenseCategoryData, expenseCategoryColors, periodLabel } = dashboardData;
    const totalExpenses = expenseCategoryData.reduce((total, amount) => total + amount, 0);
    const legend = document.getElementById('expenseCompositionLegend');

    if (totalExpenses <= 0) {
        chartEmptyState(canvas, `Belum ada pengeluaran pada ${periodLabel.toLowerCase()}.`);
        if (legend) {
            const emptyState = document.createElement('p');
            emptyState.className = 'chart-empty-state';
            emptyState.textContent = 'Belum ada rincian kategori.';
            legend.replaceChildren(emptyState);
        }
        return;
    }

    if (legend) {
        const legendItems = expenseCategoryLabels.map((label, index) => {
            const amount = Number(expenseCategoryData[index] || 0);
            const item = document.createElement('div');
            item.className = 'dashboard-expense-legend-item';

            const dot = document.createElement('span');
            dot.className = 'dashboard-expense-legend-dot';
            dot.style.backgroundColor = expenseCategoryColors[index] || '#94a3b8';

            const name = document.createElement('span');
            name.className = 'dashboard-expense-legend-name';
            name.textContent = label;

            const value = document.createElement('span');
            value.className = 'dashboard-expense-legend-value';
            value.textContent = formatRupiah(amount);

            const percentage = document.createElement('span');
            percentage.className = 'dashboard-expense-legend-percent';
            percentage.textContent = `${((amount / totalExpenses) * 100).toLocaleString('id-ID', { maximumFractionDigits: 1 })}% dari pengeluaran`;

            item.append(dot, name, value, percentage);
            return item;
        });

        legend.replaceChildren(...legendItems);
    }

    const centerTotalPlugin = {
        id: 'expenseCenterTotal',
        afterDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;
            const cardStyle = getComputedStyle(chart.canvas.closest('.content-card'));
            const totalFontSize = Math.max(12, Math.min(16, chartArea.width * 0.11));

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = cardStyle.getPropertyValue('--text-primary').trim() || '#192b52';
            ctx.font = `700 ${totalFontSize}px Poppins, sans-serif`;
            ctx.fillText(formatRupiah(totalExpenses), centerX, centerY - 8, chartArea.width * 0.82);
            ctx.fillStyle = cardStyle.getPropertyValue('--text-muted').trim() || '#718096';
            ctx.font = '11px Poppins, sans-serif';
            ctx.fillText('Total Pengeluaran', centerX, centerY + 11, chartArea.width * 0.82);
            ctx.restore();
        }
    };

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: expenseCategoryLabels,
            datasets: [{
                data: expenseCategoryData,
                backgroundColor: expenseCategoryColors,
                borderColor: getComputedStyle(canvas.closest('.content-card')).backgroundColor,
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const amount = Number(context.raw || 0);
                            const percent = totalExpenses > 0 ? (amount / totalExpenses) * 100 : 0;
                            return ` ${context.label}: ${formatRupiah(amount)} (${percent.toLocaleString('id-ID', { maximumFractionDigits: 1 })}%)`;
                        }
                    }
                }
            }
        },
        plugins: [centerTotalPlugin]
    });
}

function initRevenueTrendChart() {
    const canvas = document.getElementById('revenueTrendChart');
    if (!canvas) return;

    const { trendLabels, revenueData, periodLabel } = dashboardData;
    if (!revenueData.some((amount) => amount > 0)) {
        chartEmptyState(canvas, `Belum ada pemasukan pada ${periodLabel.toLowerCase()}.`);
        return;
    }

    const context = canvas.getContext('2d');
    const gradient = context.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(136, 214, 108, 0.24)');
    gradient.addColorStop(1, 'rgba(136, 214, 108, 0.01)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Pemasukan',
                data: revenueData,
                borderColor: DASHBOARD_REVENUE_COLOR,
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: trendLabels.length > 20 ? 0 : 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: DASHBOARD_REVENUE_COLOR,
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => ` Pemasukan: ${formatRupiah(context.parsed.y || 0)}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'Poppins', size: 10 },
                        maxTicksLimit: 10,
                        autoSkip: true
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(55, 65, 81, .12)' },
                    ticks: {
                        font: { family: 'Poppins', size: 10 },
                        callback(value) {
                            if (value >= 1000000) return `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`;
                            if (value >= 1000) return `Rp ${(value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} rb`;
                            return `Rp ${Number(value).toLocaleString('id-ID')}`;
                        }
                    }
                }
            }
        }
    });
}

function initFinancialComparisonChart() {
    const canvas = document.getElementById('financialComparisonChart');
    if (!canvas) return;

    const { trendLabels, revenueData, expenseData, periodLabel } = dashboardData;
    if (![...revenueData, ...expenseData].some((amount) => amount > 0)) {
        chartEmptyState(canvas, `Belum ada data keuangan pada ${periodLabel.toLowerCase()}.`);
        return;
    }

    const categoryPercentage = getFinancialBarCategoryPercentage(trendLabels.length);
    const barPercentage = 0.7;

    const chart = new Chart(canvas, {
        type: 'bar',
        plugins: [{
            id: 'fixed-financial-bar-gap',
            beforeDatasetsDraw(chart) {
                const revenueBars = chart.getDatasetMeta(0).data;
                const expenseBars = chart.getDatasetMeta(1).data;
                const visibleCategoryCount = Math.max(1, chart.scales.x.max - chart.scales.x.min + 1);
                const referenceCategoryWidth = chart.chartArea.width / visibleCategoryCount;
                const referenceBarWidth = Math.min(
                    32,
                    referenceCategoryWidth * categoryPercentage * barPercentage / 2
                );

                revenueBars.forEach((revenueBar, index) => {
                    const expenseBar = expenseBars[index];
                    if (!expenseBar) return;

                    const center = chart.scales.x.getPixelForValue(index);
                    revenueBar.width = referenceBarWidth;
                    expenseBar.width = referenceBarWidth;
                    revenueBar.x = center - (revenueBar.width + FINANCIAL_BAR_GAP_PX) / 2;
                    expenseBar.x = center + (expenseBar.width + FINANCIAL_BAR_GAP_PX) / 2;
                });
            }
        }],
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: revenueData,
                    backgroundColor: FINANCIAL_REVENUE_COLOR,
                    borderRadius: 4,
                    maxBarThickness: 12,
                    categoryPercentage,
                    barPercentage,
                    grouped: true
                },
                {
                    label: 'Pengeluaran',
                    data: expenseData,
                    backgroundColor: DASHBOARD_EXPENSE_COLOR,
                    borderRadius: 4,
                    maxBarThickness: 12,
                    categoryPercentage,
                    barPercentage,
                    grouped: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            datasets: {
                bar: { categoryPercentage, barPercentage }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, pointStyle: 'circle', font: { family: 'Poppins', size: 10 } }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => ` ${context.dataset.label}: ${formatRupiah(context.parsed.y || 0)}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Poppins', size: 10 }, maxTicksLimit: 12, autoSkip: true }
                },
                y: {
                    beginAtZero: true,
                    afterFit(axis) {
                        axis.width = 72;
                    },
                    grid: { color: 'rgba(55, 65, 81, .12)' },
                    ticks: {
                        font: { family: 'Poppins', size: 10 },
                        callback(value) {
                            if (value >= 1000000) return `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`;
                            if (value >= 1000) return `Rp ${(value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} rb`;
                            return `Rp ${Number(value).toLocaleString('id-ID')}`;
                        }
                    }
                }
            }
        }
    });

    attachFinancialBarZoom(chart, canvas);
}

function syncPeriodFilter() {
    const periodSelect = document.getElementById('global-period');
    if (periodSelect) periodSelect.value = dashboardData.period;
}

document.addEventListener('DOMContentLoaded', () => {
    syncPeriodFilter();
    initExpenseCompositionChart();
    initRevenueTrendChart();
    initFinancialComparisonChart();
});
