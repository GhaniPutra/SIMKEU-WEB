/**
 * Inisialisasi filter dan grafik pada halaman daftar pengeluaran.
 * Warna dan ukuran dinamis tetap bersumber dari atribut data pada Blade.
 */
// Jalankan setelah DOM selesai dirender
document.addEventListener('DOMContentLoaded', function () {
    // Kirim filter langsung saat kategori diubah, seperti perilaku sebelumnya.
    const filterForm = document.getElementById('filterForm');
    const categoryFilter = filterForm?.querySelector('select[name="kategori"]');
    categoryFilter?.addEventListener('change', () => filterForm.submit());


    // ============================================================
    // STYLE DINAMIS — Terapkan warna dan lebar dari data attributes
    // ============================================================
    document.querySelectorAll('[data-bg]').forEach(el => {
        el.style.background = el.dataset.bg;
    });
    document.querySelectorAll('[data-width]').forEach(el => {
        el.style.width = el.dataset.width + '%';
    });

    // ============================================================
    // PALET WARNA — Konsisten dengan tema SIMKEU
    // ============================================================
    const pengeluaranColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--red-400').trim();

    // ============================================================
    // DATA DARI HTML — Ambil nilai JSON dari atribut data
    // ============================================================
    const pengeluaranChartData = document.getElementById('pengeluaran-chart-data');
    const period = pengeluaranChartData?.dataset.period;
    const chartLabels        = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.chartLabels || '[]') : [];
    const chartData          = pengeluaranChartData ? JSON.parse(pengeluaranChartData.dataset.chartData || '[]') : [];
    // ============================================================
    // Konfigurasi tooltip bersama untuk semua chart
    // ============================================================
    const tooltipDefaults = {
        backgroundColor: '#192b52',         // Latar tooltip gelap
        titleFont: { family: 'Poppins', size: 12 },
        bodyFont:  { family: 'Poppins', size: 11 },
        padding: 10,
        cornerRadius: 8,
    };

    // ============================================================
    // BAR CHART — Pengeluaran per periode.
    // ============================================================
    const trendCtx = document.getElementById('trendHarianChart');
    if (trendCtx) {
        const trendLabels = chartLabels;
        const trendValues = chartData;

        if (!trendValues.some((value) => value > 0)) {
            trendCtx.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada pengeluaran pada periode ini.</p>');
            trendCtx.remove();
        } else {
        const categoryPercentage = trendValues.length <= 1 ? 0.10
            : trendValues.length <= 3 ? 0.20
                : trendValues.length <= 7 ? 0.30
                    : trendValues.length <= 14 ? 0.40 : 0.60;
        const trendChart = new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pengeluaran',
                    backgroundColor: pengeluaranColor,
                    data: trendValues,
                    borderRadius: 4,
                    maxBarThickness: 12,
                    categoryPercentage,
                    barPercentage: 0.7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            title: (items) => trendLabels[items[0].dataIndex],
                            label: (ctx) => `Pengeluaran: Rp ${ctx.parsed.y.toLocaleString('id-ID')}`
                        }
                    }
                },
                scales: {
                    x: {
                        min: 0,
                        max: trendValues.length - 1,
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            maxTicksLimit: trendValues.length > 12 ? 8 : undefined,
                            autoSkip: true,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(55, 65, 81, .12)' },
                        ticks: {
                            font: { size: 10, family: 'Poppins' },
                            callback: (value) => value >= 1000000
                                ? `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`
                                : value >= 1000
                                    ? `Rp ${(value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} rb`
                                    : `Rp ${Number(value).toLocaleString('id-ID')}`
                        }
                    }
                }
            }
        });
        window.attachFinancialBarZoom(trendChart, trendCtx);
        }
    }

}); // DOMContentLoaded
