import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { runInNewContext } from 'node:vm';
import test from 'node:test';

test('keeps the Pemasukan vs Pengeluaran bar gap fixed across periods', async () => {
    let config;
    const listeners = {};
    const listenerOptions = {};
    const canvas = {
        addEventListener(name, callback, options) {
            listeners[name] = callback;
            listenerOptions[name] = options;
        },
        getBoundingClientRect() {
            return { left: 0, top: 0, width: 600, height: 300 };
        }
    };
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
            documentElement: {},
            getElementById(id) {
                if (id === 'dashboard-data') return dashboardElement;
                return id === 'financialComparisonChart' ? canvas : null;
            },
            addEventListener(_, callback) {
                callback();
            }
        },
        getComputedStyle() {
            return { getPropertyValue: () => '' };
        },
        Chart: function (_, chartConfig) {
            config = chartConfig;
            const chart = {
                data: chartConfig.data,
                options: chartConfig.options,
                chartArea: { left: 0, right: 600, width: 600 },
                scales: {
                    x: {
                        min: 0,
                        max: labels.length - 1,
                        getValueForPixel: (pixel) => pixel / 20
                    }
                },
                update() {
                    this.scales.x.min = this.options.scales.x.min;
                    this.scales.x.max = this.options.scales.x.max;
                },
                getDatasetMeta(index) {
                    return { data: chartConfig.data.datasets[index].data.map(() => ({ x: 0, width: 0 })) };
                }
            };

            return chart;
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
            scales: { x: { min: 0, max: count - 1, getPixelForValue: (index) => index * 20 } },
            getDatasetMeta: (index) => ({ data: index === 0 ? revenueBars : expenseBars })
        };

        gapPlugin.beforeDatasetsDraw(chart);
        const expectedWidth = Math.min(32, (600 / count) * 0.6 * 0.7 / 2);
        revenueBars.forEach((bar) => assert.ok(Math.abs(bar.width - expectedWidth) < 1e-9));
        expenseBars.forEach((bar) => assert.ok(Math.abs(bar.width - expectedWidth) < 1e-9));
        return expenseBars.map((expenseBar, index) => expenseBar.x - expenseBar.width / 2
            - revenueBars[index].x - revenueBars[index].width / 2);
    }).flat();

    assert.ok(gaps.every((gap) => Math.abs(gap - 3) < 1e-9));

    assert.equal(typeof listeners.wheel, 'function');
    assert.equal(listenerOptions.wheel.passive, false);
    assert.equal(typeof listeners.dblclick, 'function');
    assert.ok(config.options.plugins.tooltip);
    assert.equal(config.options.interaction.mode, 'index');
    assert.equal(config.options.interaction.intersect, false);
});

test('zooms for mouse wheel and trackpad pinch, ignores ordinary trackpad scrolling, and resets on double-click', async () => {
    let listeners = {};
    let chartInstance;
    const canvas = {
        style: { cursor: '' },
        addEventListener(name, callback) {
            listeners[name] = callback;
        },
        setPointerCapture(pointerId) {
            this.capturedPointerId = pointerId;
        },
        getBoundingClientRect() {
            return { left: 0, top: 0, width: 600, height: 300 };
        }
    };
    const labels = Array.from({ length: 31 }, (_, index) => String(index + 1));
    const dashboardElement = {
        dataset: {
            period: 'month',
            periodLabel: 'Bulan Ini',
            allPeriodMonthCount: '31',
            trendLabels: JSON.stringify(labels),
            revenueData: JSON.stringify(labels.map((_, index) => (index + 1) * 1000)),
            expenseData: JSON.stringify(labels.map((_, index) => (index + 1) * 500)),
            expenseCategoryLabels: '[]',
            expenseCategoryData: '[]',
            expenseCategoryColors: '[]'
        }
    };
    const context = {
        document: {
            documentElement: {},
            getElementById(id) {
                if (id === 'dashboard-data') return dashboardElement;
                return id === 'financialComparisonChart' ? canvas : null;
            },
            addEventListener(_, callback) {
                callback();
            }
        },
        getComputedStyle() {
            return { getPropertyValue: () => '' };
        },
        Chart: function (_, config) {
            chartInstance = {
                data: config.data,
                options: config.options,
                chartArea: { left: 0, right: 600, width: 600 },
                scales: {
                    x: {
                        min: 0,
                        max: labels.length - 1,
                        getValueForPixel: (pixel) => pixel / 20
                    }
                },
                update() {
                    this.scales.x.min = this.options.scales.x.min;
                    this.scales.x.max = this.options.scales.x.max;
                }
            };

            return chartInstance;
        }
    };
    const source = await readFile(new URL('../../public/js/dashboard/dashboard.js', import.meta.url), 'utf8');
    runInNewContext(source, context);

    const initialMax = chartInstance.scales.x.max;
    listeners.pointerdown({ pointerId: 0, pointerType: 'mouse', button: 0, clientX: 300 });
    assert.equal(canvas.capturedPointerId, undefined);
    assert.equal(canvas.style.cursor, '');

    let wheelPrevented = false;
    listeners.wheel({
        deltaY: -120,
        deltaMode: 1,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { wheelPrevented = true; }
    });

    assert.ok(wheelPrevented);
    assert.ok(chartInstance.scales.x.max - chartInstance.scales.x.min < initialMax);
    assert.ok(chartInstance.options.scales.y.max > 0);

    const beforeTrackpadScroll = {
        min: chartInstance.scales.x.min,
        max: chartInstance.scales.x.max
    };
    let trackpadScrollPrevented = false;
    listeners.wheel({
        deltaY: 48,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { trackpadScrollPrevented = true; }
    });
    assert.equal(trackpadScrollPrevented, false);
    assert.equal(chartInstance.scales.x.min, beforeTrackpadScroll.min);
    assert.equal(chartInstance.scales.x.max, beforeTrackpadScroll.max);

    const zoomedCount = beforeTrackpadScroll.max - beforeTrackpadScroll.min + 1;
    const verticalMaxBeforePan = chartInstance.options.scales.y.max;
    assert.equal(canvas.style.cursor, 'grab');

    for (let index = 0; index < 3; index += 1) {
        let smallDeltaPrevented = false;
        listeners.wheel({
            deltaX: 8,
            deltaY: 0,
            deltaMode: 0,
            clientX: 300,
            ctrlKey: false,
            preventDefault() { smallDeltaPrevented = true; }
        });
        assert.ok(smallDeltaPrevented);
    }
    assert.equal(chartInstance.scales.x.min, beforeTrackpadScroll.min);

    let accumulatedPanPrevented = false;
    listeners.wheel({
        deltaX: 8,
        deltaY: 0,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { accumulatedPanPrevented = true; }
    });
    assert.ok(accumulatedPanPrevented);
    assert.equal(chartInstance.scales.x.min, beforeTrackpadScroll.min + 1);

    let trackpadPanPrevented = false;
    listeners.wheel({
        deltaX: 120,
        deltaY: 4,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { trackpadPanPrevented = true; }
    });
    const afterTrackpadRight = chartInstance.scales.x.min;
    assert.ok(trackpadPanPrevented);
    assert.ok(afterTrackpadRight > beforeTrackpadScroll.min);
    assert.equal(chartInstance.scales.x.max - chartInstance.scales.x.min + 1, zoomedCount);
    assert.equal(chartInstance.options.scales.y.max, verticalMaxBeforePan);

    let trackpadPanLeftPrevented = false;
    listeners.wheel({
        deltaX: -120,
        deltaY: 4,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { trackpadPanLeftPrevented = true; }
    });
    assert.ok(trackpadPanLeftPrevented);
    assert.ok(chartInstance.scales.x.min < afterTrackpadRight);
    assert.equal(chartInstance.scales.x.max - chartInstance.scales.x.min + 1, zoomedCount);
    assert.equal(chartInstance.options.scales.y.max, verticalMaxBeforePan);

    listeners.wheel({
        deltaX: 50000,
        deltaY: 0,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: false,
        preventDefault() {}
    });
    assert.equal(chartInstance.scales.x.max, initialMax);
    let boundaryPanPrevented = false;
    listeners.wheel({
        deltaX: 50000,
        deltaY: 0,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { boundaryPanPrevented = true; }
    });
    assert.equal(boundaryPanPrevented, false);
    assert.equal(chartInstance.scales.x.max, initialMax);

    const beforePageScroll = { min: chartInstance.scales.x.min, max: chartInstance.scales.x.max };
    let pageScrollPrevented = false;
    listeners.wheel({
        deltaY: 1,
        deltaMode: 2,
        clientX: 300,
        ctrlKey: false,
        preventDefault() { pageScrollPrevented = true; }
    });
    assert.equal(pageScrollPrevented, false);
    assert.equal(chartInstance.scales.x.min, beforePageScroll.min);
    assert.equal(chartInstance.scales.x.max, beforePageScroll.max);

    let pinchPrevented = false;
    listeners.wheel({
        deltaY: -120,
        deltaMode: 0,
        clientX: 300,
        ctrlKey: true,
        preventDefault() { pinchPrevented = true; }
    });
    assert.ok(pinchPrevented);
    assert.ok(chartInstance.scales.x.max - chartInstance.scales.x.min + 1 < zoomedCount);

    const verticalMaxBeforeDrag = chartInstance.options.scales.y.max;
    const dragMinBefore = chartInstance.scales.x.min;
    const dragCount = chartInstance.scales.x.max - chartInstance.scales.x.min + 1;
    listeners.pointerdown({
        pointerId: 1,
        pointerType: 'mouse',
        button: 0,
        clientX: 300
    });
    assert.equal(canvas.capturedPointerId, 1);
    assert.equal(canvas.style.cursor, 'grabbing');
    listeners.pointermove({
        pointerId: 1,
        clientX: 200,
        cancelable: true,
        preventDefault() {}
    });
    const dragRightMin = chartInstance.scales.x.min;
    assert.ok(dragRightMin > dragMinBefore);
    assert.equal(chartInstance.scales.x.max - chartInstance.scales.x.min + 1, dragCount);
    assert.equal(chartInstance.options.scales.y.max, verticalMaxBeforeDrag);
    listeners.pointerup({ pointerId: 1 });
    assert.equal(canvas.style.cursor, 'grab');

    listeners.pointerdown({
        pointerId: 2,
        pointerType: 'mouse',
        button: 0,
        clientX: 300
    });
    listeners.pointermove({
        pointerId: 2,
        clientX: 400,
        cancelable: true,
        preventDefault() {}
    });
    assert.ok(chartInstance.scales.x.min < dragRightMin);
    listeners.pointerup({ pointerId: 2 });

    listeners.pointerdown({ pointerId: 3, pointerType: 'mouse', button: 0, clientX: 300 });
    listeners.pointermove({ pointerId: 3, clientX: -50000, cancelable: true, preventDefault() {} });
    assert.equal(chartInstance.scales.x.max, initialMax);
    listeners.pointerup({ pointerId: 3 });

    listeners.pointerdown({ pointerId: 4, pointerType: 'mouse', button: 0, clientX: 300 });
    listeners.pointermove({ pointerId: 4, clientX: 50000, cancelable: true, preventDefault() {} });
    assert.equal(chartInstance.scales.x.min, 0);
    listeners.pointerup({ pointerId: 4 });

    let resetPrevented = false;
    listeners.dblclick({
        preventDefault() { resetPrevented = true; }
    });
    assert.ok(resetPrevented);
    assert.equal(chartInstance.scales.x.min, 0);
    assert.equal(chartInstance.scales.x.max, initialMax);
    assert.equal(Object.hasOwn(chartInstance.options.scales.y, 'max'), false);
    assert.equal(canvas.style.cursor, '');

    let unzoomedTrackpadPanPrevented = false;
    listeners.wheel({
        deltaX: 120,
        deltaY: 0,
        deltaMode: 0,
        ctrlKey: false,
        clientX: 300,
        preventDefault() { unzoomedTrackpadPanPrevented = true; }
    });
    assert.equal(unzoomedTrackpadPanPrevented, false);
    assert.equal(chartInstance.scales.x.min, 0);
    assert.equal(chartInstance.scales.x.max, initialMax);

    let legacyMouseWheelPrevented = false;
    listeners.wheel({
        deltaY: -100,
        deltaMode: 0,
        wheelDeltaY: 120,
        ctrlKey: false,
        clientX: 300,
        preventDefault() { legacyMouseWheelPrevented = true; }
    });
    assert.ok(legacyMouseWheelPrevented);
    assert.ok(chartInstance.scales.x.max - chartInstance.scales.x.min < initialMax);
});

test('uses the Firefox/Linux line-mode wheel heuristic and leaves pixel/page scrolling to the browser', async () => {
    const listeners = {};
    let chartInstance;
    const canvas = {
        addEventListener(name, callback, options) {
            listeners[name] = callback;
            if (name === 'wheel') assert.equal(options.passive, false);
        },
        getBoundingClientRect() {
            return { left: 0, top: 0, width: 600, height: 300 };
        }
    };
    const labels = Array.from({ length: 10 }, (_, index) => String(index + 1));
    const dashboardElement = {
        dataset: {
            period: 'month',
            periodLabel: 'Bulan Ini',
            allPeriodMonthCount: '10',
            trendLabels: JSON.stringify(labels),
            revenueData: JSON.stringify(labels.map(() => 1000)),
            expenseData: JSON.stringify(labels.map(() => 500)),
            expenseCategoryLabels: '[]',
            expenseCategoryData: '[]',
            expenseCategoryColors: '[]'
        }
    };
    const context = {
        document: {
            documentElement: {},
            getElementById(id) {
                if (id === 'dashboard-data') return dashboardElement;
                return id === 'financialComparisonChart' ? canvas : null;
            },
            addEventListener(_, callback) {
                callback();
            }
        },
        getComputedStyle() {
            return { getPropertyValue: () => '' };
        },
        Chart: function (_, config) {
            chartInstance = {
                data: config.data,
                options: config.options,
                chartArea: { left: 0, right: 600, width: 600 },
                scales: {
                    x: {
                        min: 0,
                        max: labels.length - 1,
                        getValueForPixel: (pixel) => pixel / 60
                    }
                },
                update() {
                    this.scales.x.min = this.options.scales.x.min;
                    this.scales.x.max = this.options.scales.x.max;
                }
            };

            return chartInstance;
        }
    };
    const source = await readFile(new URL('../../public/js/dashboard/dashboard.js', import.meta.url), 'utf8');
    runInNewContext(source, context);

    const initialRange = chartInstance.scales.x.max - chartInstance.scales.x.min;
    let mouseWheelPrevented = false;
    listeners.wheel({
        deltaY: -3,
        deltaMode: 1,
        ctrlKey: false,
        clientX: 300,
        preventDefault() { mouseWheelPrevented = true; }
    });
    assert.ok(mouseWheelPrevented);
    assert.ok(chartInstance.scales.x.max - chartInstance.scales.x.min < initialRange);

    const zoomedRange = chartInstance.scales.x.max - chartInstance.scales.x.min;
    let ordinaryScrollPrevented = false;
    listeners.wheel({
        deltaY: -3,
        deltaMode: 0,
        ctrlKey: false,
        clientX: 300,
        preventDefault() { ordinaryScrollPrevented = true; }
    });
    assert.equal(ordinaryScrollPrevented, false);
    assert.equal(chartInstance.scales.x.max - chartInstance.scales.x.min, zoomedRange);

    let pageScrollPrevented = false;
    listeners.wheel({
        deltaY: 1,
        deltaMode: 2,
        ctrlKey: false,
        clientX: 300,
        preventDefault() { pageScrollPrevented = true; }
    });
    assert.equal(pageScrollPrevented, false);
    assert.equal(chartInstance.scales.x.max - chartInstance.scales.x.min, zoomedRange);
});
