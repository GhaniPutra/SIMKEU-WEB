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

    const referenceMonthCount = Math.max(1, dashboardData.allPeriodMonthCount || trendLabels.length);
    const categoryPercentage = getFinancialBarCategoryPercentage(referenceMonthCount);
    const barPercentage = 0.7;

    new Chart(canvas, {
        type: 'bar',
        plugins: [{
            id: 'fixed-financial-bar-gap',
            beforeDatasetsDraw(chart) {
                const revenueBars = chart.getDatasetMeta(0).data;
                const expenseBars = chart.getDatasetMeta(1).data;
                const referenceCategoryWidth = chart.chartArea.width / referenceMonthCount;
                const referenceBarWidth = Math.min(
                    12,
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
