import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { runInNewContext } from 'node:vm';
import test from 'node:test';

test('keeps the Bulan Ini gap across category counts without resizing bars', async () => {
    let config;
    const canvas = {};
    const context = {
        window: {
            dashboardData: {
                period: 'month',
                periodLabel: 'Bulan Ini',
                monthDayCount: 31,
                trendLabels: ['day'],
                revenueData: [1],
                expenseData: [1],
            },
        },
        document: {
            documentElement: {},
            getElementById: () => canvas,
            addEventListener: (_, callback) => callback(),
        },
        getComputedStyle: () => ({ getPropertyValue: () => '#000' }),
        Chart: function (_, chartConfig) {
            config = chartConfig;
        },
    };
    const source = await readFile(new URL('../../public/js/dashboard.js', import.meta.url), 'utf8');
    runInNewContext(source, context);

    const plugin = config.plugins[0];
    const gaps = [1, 7, 31, 120].map((count) => {
        const revenueBars = Array.from({ length: count }, () => ({ x: 0, width: 4 }));
        const expenseBars = Array.from({ length: count }, () => ({ x: 0, width: 12 }));
        const chart = {
            chartArea: { width: 1000 },
            scales: { x: { getPixelForValue: (index) => index * 20 } },
            getDatasetMeta: (index) => ({ data: index === 0 ? revenueBars : expenseBars }),
        };

        plugin.beforeDatasetsDraw(chart);
        assert.equal(revenueBars[0].width, 4);
        assert.equal(expenseBars[0].width, 12);
        return expenseBars[0].x - expenseBars[0].width / 2
            - revenueBars[0].x - revenueBars[0].width / 2;
    });

    assert.ok(Math.abs(gaps[0] - (1000 * 0.6 / 31 / 2 * 0.3)) < 1e-9);
    assert.ok(gaps.every((gap) => Math.abs(gap - gaps[0]) < 1e-9));
});
