import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { runInNewContext } from 'node:vm';
import test from 'node:test';

test('keeps the Pemasukan vs Pengeluaran bar gap fixed across periods', async () => {
    let config;
    const canvas = {};
    const labels = Array.from({ length: 31 }, (_, index) => String(index + 1));
    const dashboardElement = {
        dataset: {
            period: 'month',
            periodLabel: 'Bulan Ini',
            allPeriodMonthCount: '10',
            trendLabels: JSON.stringify(labels),
            revenueData: JSON.stringify(labels.map(() => 1)),
            expenseData: JSON.stringify(labels.map(() => 1)),
            expenseCategoryLabels: '[]',
            expenseCategoryData: '[]',
            expenseCategoryColors: '[]'
        }
    };
    const context = {
        document: {
            getElementById(id) {
                if (id === 'dashboard-data') return dashboardElement;
                return id === 'financialComparisonChart' ? canvas : null;
            },
            addEventListener(_, callback) {
                callback();
            }
        },
        Chart: function (_, chartConfig) {
            config = chartConfig;
        }
    };
    const source = await readFile(new URL('../../public/js/dashboard/dashboard.js', import.meta.url), 'utf8');
    runInNewContext(source, context);

    const gapPlugin = config.plugins.find((plugin) => plugin.id === 'fixed-financial-bar-gap');
    assert.ok(gapPlugin);

    const gaps = [1, 7, 31, 120].map((count) => {
        const revenueBars = Array.from({ length: count }, (_, index) => ({ x: 0, width: index % 2 ? 12 : 6.5 }));
        const expenseBars = Array.from({ length: count }, (_, index) => ({ x: 0, width: index % 2 ? 8 : 11 }));
        const chart = {
            chartArea: { width: 600 },
            scales: { x: { getPixelForValue: (index) => index * 20 } },
            getDatasetMeta: (index) => ({ data: index === 0 ? revenueBars : expenseBars })
        };

        gapPlugin.beforeDatasetsDraw(chart);
        revenueBars.forEach((bar) => assert.ok(Math.abs(bar.width - 8.4) < 1e-9));
        expenseBars.forEach((bar) => assert.ok(Math.abs(bar.width - 8.4) < 1e-9));
        return expenseBars.map((expenseBar, index) => expenseBar.x - expenseBar.width / 2
            - revenueBars[index].x - revenueBars[index].width / 2);
    }).flat();

    assert.ok(gaps.every((gap) => Math.abs(gap - 3) < 1e-9));
});
