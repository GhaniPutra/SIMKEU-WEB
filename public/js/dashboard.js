// ============================================================
// dashboard.js — JavaScript Halaman Dashboard SIMKEU
//
// File ini bertanggung jawab untuk:
//   1. Menginisialisasi grafik tren pendapatan vs pengeluaran
//      dengan tampilan yang berbeda sesuai periode filter:
//      - 'all'   → seluruh riwayat, agregat per bulan
//      - 'month' → bulan berjalan, total per hari
//      - 'week'  → minggu berjalan, total per hari
//      - 'today' → 24 jam (pengeluaran tercatat pada jam 00)
//
// Data grafik dikirim dari Blade view melalui variabel global
// window.dashboardData yang didefinisikan di dashboard.blade.php.
// ============================================================

// ============================================================
// Ambil data grafik yang dikirim dari server (via Blade/PHP)
// Jika window.dashboardData tidak terdefinisi, gunakan array kosong
// sebagai nilai default agar tidak terjadi error JavaScript
// ============================================================
const {
    period,       // Periode aktif: 'all', 'today', 'week', atau 'month'
    periodLabel,  // Label periode: 'Hari Ini', 'Minggu Ini', 'Bulan Ini'
    monthDayCount,
    trendLabels,  // Array label untuk sumbu X grafik
    revenueData,  // Array angka pendapatan
    expenseData   // Array angka pengeluaran
} = window.dashboardData || {
    period      : 'month',
    periodLabel : 'Bulan Ini',
    monthDayCount: 30,
    trendLabels : [],
    revenueData : [],
    expenseData : []
};

// ============================================================
// Fungsi: formatRupiah
// Tujuan: Format angka menjadi string Rupiah untuk tooltip
// ============================================================
function formatRupiah(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

// ============================================================
// Fungsi: getChartTitle
// Tujuan: Mengembalikan subtitle grafik sesuai periode aktif
// ============================================================
function getChartTitle() {
    switch (period) {
        case 'all':   return 'Per Bulan — Seluruh Riwayat';
        case 'today': return 'Total Hari Ini';
        case 'week':  return 'Per Hari — Minggu Ini';
        case 'month': return 'Per Hari — Bulan Ini';
        default:      return periodLabel;
    }
}

// ============================================================
// Fungsi: createChartDefaults
// Tujuan: Menghasilkan objek konfigurasi default untuk Chart.js
//         yang digunakan secara konsisten oleh semua grafik.
//         Konfigurasi disesuaikan dengan periode filter aktif.
// ============================================================
function createChartDefaults() {
    const gridLine = 'rgba(55,65,81,.14)';
    const moneyTick = (value) => {
        if (value >= 1000000) return `Rp ${(value / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} jt`;
        if (value >= 1000) return `Rp ${(value / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} rb`;
        return `Rp ${value.toLocaleString('id-ID')}`;
    };

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },

        plugins: {
            subtitle: {
                display: true,
                text: getChartTitle(),
                font: { size: 12, family: 'Poppins', weight: '500' },
                color: '#374151',
                padding: { bottom: 12 }
            },
            legend: {
                position: 'bottom',
                labels: { font: { size: 11, family: 'Poppins' } }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: (context) => `${context.dataset.label}: ${formatRupiah(context.parsed.y ?? 0)}`
                }
            }
        },
        scales: {
            x: {
                type: 'category',
                stacked: false,
                grid: { display: false },
                ticks: { font: { size: 10, family: 'Poppins' }, maxTicksLimit: period === 'today' ? 9 : 12, autoSkip: true }
            },
            y: {
                stacked: false,
                grid: { color: gridLine },
                ticks: { font: { size: 10, family: 'Poppins' }, callback: moneyTick },
                beginAtZero: true,
                grace: '5%'
            }
        }
    };
}

// ============================================================
// Fungsi: getCategoryPercentage
// Tujuan: Menyesuaikan proporsi lebar grup kategori berdasarkan
//         jumlah data pada sumbu X agar batang perbandingan tetap
//         berdekatan dan proporsional di semua periode.
// ============================================================
function getCategoryPercentage(numLabels) {
    if (numLabels <= 1) return 0.10;
    if (numLabels <= 3) return 0.20;
    if (numLabels <= 7) return 0.30;
    if (numLabels <= 14) return 0.40;
    return 0.60;
}

// ============================================================
// Fungsi: initTrendChart
// Tujuan: Menginisialisasi grafik tren keuangan yang
//         membandingkan pendapatan vs pengeluaran per periode.
// ============================================================
function initTrendChart() {
    const trendCanvas = document.getElementById('trendChart');
    if (!trendCanvas) return;

    const labels = trendLabels;
    const revenue = revenueData;
    const expenses = expenseData;

    if (!revenue.some((value) => value > 0) && !expenses.some((value) => value > 0)) {
        trendCanvas.insertAdjacentHTML('afterend', '<p class="chart-empty-state">Belum ada data pada periode ini.</p>');
        trendCanvas.remove();
        return;
    }

    const categoryPercentage = getCategoryPercentage(labels.length);
    const barPercentage = 0.7;
    const chartColors = getComputedStyle(document.documentElement);
    const revenueColor = chartColors.getPropertyValue('--blue-400').trim();
    const expenseColor = chartColors.getPropertyValue('--red-400').trim();

    new Chart(trendCanvas, {
        type: 'bar',
        plugins: [{
            id: 'fixed-grouped-bar-gap',
            beforeDatasetsDraw(chart) {
                const monthCategoryChunk = chart.chartArea.width * getCategoryPercentage(monthDayCount) / monthDayCount / 2;
                const gap = monthCategoryChunk - Math.min(12, monthCategoryChunk * 0.7);
                const revenueBars = chart.getDatasetMeta(0).data;
                const expenseBars = chart.getDatasetMeta(1).data;

                revenueBars.forEach((revenueBar, index) => {
                    const expenseBar = expenseBars[index];
                    if (!expenseBar) return;

                    const center = chart.scales.x.getPixelForValue(index);
                    revenueBar.x = center - (revenueBar.width + gap) / 2;
                    expenseBar.x = center + (expenseBar.width + gap) / 2;
                });
            }
        }],

        data: {
            labels,
            datasets: [
                {
                    label: 'Pendapatan',
                    backgroundColor: revenueColor,
                    data: revenue,
                    borderRadius: 0,
                    maxBarThickness: 12,
                    categoryPercentage,
                    barPercentage,
                    grouped: true
                },
                {
                    label: 'Pengeluaran',
                    backgroundColor: expenseColor,
                    data: expenses,
                    borderRadius: 0,
                    maxBarThickness: 12,
                    categoryPercentage,
                    barPercentage,
                    grouped: true
                }
            ]
        },
        options: {
            ...createChartDefaults(),
            datasets: {
                bar: {
                    categoryPercentage,
                    barPercentage
                }
            }
        }
    });
}

// ============================================================
// Fungsi: initDashboard
// Tujuan: Entry point utama — memanggil semua fungsi inisialisasi
//         yang perlu dijalankan saat halaman dashboard dimuat.
// ============================================================
function initDashboard() {
    initTrendChart();  // Inisialisasi dan render grafik tren keuangan
}

// ============================================================
// Event: DOMContentLoaded
// Jalankan initDashboard() setelah seluruh elemen DOM selesai
// diparse oleh browser (sebelum gambar dan stylesheet selesai).
// Ini memastikan semua elemen canvas sudah tersedia saat
// Chart.js mencoba mengaksesnya.
// ============================================================
document.addEventListener('DOMContentLoaded', initDashboard);
