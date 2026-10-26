import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const config = window.SimkeuRealtimeConfig;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const seenEventIds = new Set();
const failedSubscriptions = new Set();
let refreshInFlight = false;
let refreshPending = false;

if (config?.storeId && config.resources?.length) {
    window.Pusher = Pusher;

    const echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 80),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': csrfToken } },
    });

    window.Echo = echo;

    const refreshFragments = async (changedResources = config.resources) => {
        if (refreshInFlight) {
            refreshPending = true;
            return;
        }

        const fragments = [...document.querySelectorAll('[data-realtime-fragment]')]
            .filter((fragment) => fragment.dataset.realtimeResource
                .split(',')
                .some((resource) => changedResources.includes(resource)));
        if (!fragments.length) return;
        if (document.querySelector('.modal.show') ||
            fragments.some((fragment) => fragment.contains(document.activeElement))) {
            refreshPending = true;
            return;
        }

        refreshInFlight = true;
        try {
            const response = await fetch(window.location.href, {
                headers: { Accept: 'text/html', 'X-Realtime-Sync': '1' },
                cache: 'no-store',
            });
            if (!response.ok) throw new Error(`Realtime refresh failed: ${response.status}`);

            const nextPage = new DOMParser().parseFromString(await response.text(), 'text/html');
            fragments.forEach((fragment) => {
                const nextFragment = nextPage.querySelector(
                    `[data-realtime-fragment="${fragment.dataset.realtimeFragment}"]`,
                );
                if (nextFragment) fragment.innerHTML = nextFragment.innerHTML;
            });

            window.SimkeuRealtime?.updateCharts?.(nextPage);
        } catch (error) {
            console.warn('Realtime page synchronization failed.', error);
        } finally {
            refreshInFlight = false;
            if (refreshPending) {
                refreshPending = false;
                queueMicrotask(refreshFragments);
            }
        }
    };

    const handleChange = (resource, payload) => {
        const eventKey = `${payload?.id}:${resource}`;
        if (!payload?.id || seenEventIds.has(eventKey)) return;
        seenEventIds.add(eventKey);
        if (seenEventIds.size > 200) seenEventIds.delete(seenEventIds.values().next().value);

        window.dispatchEvent(new CustomEvent('simkeu:data-changed', {
            detail: { id: payload.id, resources: [resource] },
        }));
        refreshFragments([resource]);
    };

    for (const resource of config.resources) {
        const channel = echo.private(`store.${config.storeId}.${resource}`);
        channel
            .listen('.store.data.changed', (payload) => handleChange(resource, payload))
            .subscribed(() => failedSubscriptions.delete(resource))
            .error((error) => {
                failedSubscriptions.add(resource);
                console.warn(`Realtime subscription failed for ${resource}.`, error);
            });
    }

    let wasConnected = false;
    echo.connector.pusher.connection.bind('state_change', ({ current }) => {
        const connected = current === 'connected';
        if (connected && wasConnected) refreshFragments();
        wasConnected = connected;
    });
    echo.connector.pusher.connection.bind('error', (error) => {
        console.warn('Realtime connection failed.', error);
    });

    window.setInterval(() => {
        if (echo.connector.pusher.connection.state !== 'connected' ||
            failedSubscriptions.size > 0) refreshFragments();
    }, 60000);

    window.SimkeuRealtime = {
        updateCharts(nextPage) {
            const readData = (element, key) => JSON.parse(element?.dataset[key] || '[]');
            const updateChart = (id, labels, values, colors) => {
                const canvas = document.getElementById(id);
                const chart = canvas && window.Chart?.getChart(canvas);
                if (!chart) return;
                chart.data.labels = labels;
                chart.data.datasets[0].data = values;
                if (colors) chart.data.datasets[0].backgroundColor = colors;
                chart.update('none');
            };

            const dashboardData = nextPage.getElementById('dashboard-data');
            if (dashboardData) {
                window.SimkeuDashboardRefresh?.({
                    trendLabels: readData(dashboardData, 'trendLabels'),
                    revenueData: readData(dashboardData, 'revenueData').map(Number),
                    expenseData: readData(dashboardData, 'expenseData').map(Number),
                    expenseCategoryLabels: readData(dashboardData, 'expenseCategoryLabels'),
                    expenseCategoryData: readData(dashboardData, 'expenseCategoryData').map(Number),
                    expenseCategoryColors: readData(dashboardData, 'expenseCategoryColors'),
                });
            }

            const salesData = nextPage.getElementById('pemasukan-chart-data');
            if (salesData) {
                updateChart(
                    'topProductsChart',
                    readData(salesData, 'productLabels'),
                    readData(salesData, 'productValues').map(Number),
                );
                updateChart(
                    'metodeChart',
                    readData(salesData, 'metodeLabels'),
                    readData(salesData, 'metodeData').map(Number),
                );
            }

            const expenseData = nextPage.getElementById('pengeluaran-chart-data');
            if (expenseData) {
                updateChart(
                    'trendHarianChart',
                    readData(expenseData, 'chartLabels'),
                    readData(expenseData, 'chartData').map(Number),
                );
            }
        },
    };

    window.addEventListener('focusout', () => {
        if (refreshPending) refreshFragments();
    });
    document.addEventListener('hidden.bs.modal', () => {
        if (refreshPending) refreshFragments();
    });
}
