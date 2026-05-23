// ============================================================
// dashboard.js — JavaScript Halaman Dashboard SIMKEU
//
// File ini bertanggung jawab untuk:
//   1. Animasi elemen saat halaman pertama kali dimuat (fade-up)
//   2. Menginisialisasi grafik tren pendapatan vs pengeluaran
//      dengan tampilan yang berbeda sesuai periode filter:
//      - 'month' → 12 bulan (Jan–Des), total per bulan
//      - 'week'  → 7 hari (Senin–Minggu), total per hari
//      - 'today' → 24 jam (00:00–23:00), total per jam
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
    period,       // Periode aktif: 'today', 'week', atau 'month'
    periodLabel,  // Label periode: 'Hari Ini', 'Minggu Ini', 'Bulan Ini'
    trendLabels,  // Array label untuk sumbu X grafik
    revenueData,  // Array angka pendapatan
    expenseData   // Array angka pengeluaran
} = window.dashboardData || {
    period      : 'month',
    periodLabel : 'Bulan Ini',
    trendLabels : [],
    revenueData : [],
    expenseData : []
};

// ============================================================
// Fungsi: animateFadeUp
// Tujuan: Membuat efek animasi muncul dari bawah (fade-up)
//         pada semua elemen yang memiliki class 'fade-up'.
//
// Animasi dibuat secara staggered (bertahap) dengan delay
// berbeda untuk setiap elemen, sehingga elemen muncul
// satu per satu dari atas ke bawah halaman.
// ============================================================
function animateFadeUp() {
    // Pilih semua elemen yang memiliki class 'fade-up'
    const elements = document.querySelectorAll('.fade-up');

    // Iterasi setiap elemen dan tambahkan class 'visible' dengan delay bertahap
    elements.forEach((element, index) => {
        // Setiap elemen mendapat delay tambahan 80ms dari elemen sebelumnya
        // Elemen ke-0: 0ms, Elemen ke-1: 80ms, Elemen ke-2: 160ms, dst.
        setTimeout(() => {
            element.classList.add('visible'); // Class 'visible' memicu animasi CSS
        }, index * 80); // Delay staggered: 80ms per elemen
    });
}

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
        case 'today': return 'Per Jam — Hari Ini';
        case 'week':  return 'Per Hari — Minggu Ini';
        case 'month': return 'Per Bulan — Tahun Ini';
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
    const gridLine = '#e2e8f0'; // Warna garis grid grafik (abu-abu muda)

    return {
        responsive        : true,  // Grafik menyesuaikan lebar container-nya
        maintainAspectRatio: false, // Tinggi grafik dikontrol oleh CSS, bukan rasio aspek

        plugins: {
            // Sub-judul grafik: menampilkan info periode
            subtitle: {
                display : true,
                text    : getChartTitle(),
                font    : { size: 12, family: 'Inter', weight: '500' },
                color   : '#64748b',
                padding : { bottom: 12 }
            },

            // Konfigurasi legenda grafik (label dataset di bawah grafik)
            legend: {
                position: 'bottom', // Legenda ditempatkan di bawah grafik
                labels  : {
                    boxWidth: 10,                          // Lebar kotak warna pada legenda
                    font    : { size: 11, family: 'Inter' }, // Font legenda: Inter 11px
                    padding : 14                           // Jarak antar item legenda
                }
            },

            // Konfigurasi tooltip (popup info saat hover pada data point)
            tooltip: {
                mode           : 'index',     // Tampilkan semua dataset pada titik X yang sama
                intersect      : false,        // Tooltip muncul meski kursor tidak tepat di titik
                backgroundColor: '#1e293b',   // Warna latar tooltip: dark navy
                titleFont      : { family: 'Inter', size: 12 }, // Font judul tooltip
                bodyFont       : { family: 'Inter', size: 11 }, // Font isi tooltip
                padding        : 10,           // Padding dalam tooltip
                cornerRadius   : 8,           // Sudut rounded pada kotak tooltip

                // Format nilai tooltip menjadi Rupiah
                callbacks: {
                    label: function (context) {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y || 0;
                        return label + ': ' + formatRupiah(value);
                    }
                }
            }
        },

        scales: {
            // Konfigurasi sumbu X (horizontal)
            x: {
                grid : { color: gridLine, drawBorder: false },
                ticks: {
                    font    : { size: 10, family: 'Inter' },
                    // Untuk periode harian (24 jam), tampilkan hanya setiap 3 jam
                    // agar label tidak terlalu rapat
                    maxTicksLimit: period === 'today' ? 9 : undefined
                }
            },

            // Konfigurasi sumbu Y (vertikal — nilai rupiah)
            y: {
                grid : { color: gridLine, drawBorder: false },
                ticks: {
                    font    : { size: 10, family: 'Inter' },
                    // Format label Y menjadi singkatan (ribuan/jutaan)
                    callback: function (value) {
                        if (value >= 1000000) return (value / 1000000).toFixed(1) + ' jt';
                        if (value >= 1000)    return (value / 1000).toFixed(0) + ' rb';
                        return value;
                    }
                },
                beginAtZero: true // Mulai dari 0 agar proporsi terlihat jelas
            }
        }
    };
}

// ============================================================
// Fungsi: getChartType
// Tujuan: Menentukan tipe grafik sesuai periode
//   - 'today' → line chart (area)
//   - 'week'  → line chart (area)
//   - 'month' → line chart (area)
// ============================================================
function getChartType() {
    return 'line';
}

// ============================================================
// Fungsi: initTrendChart
// Tujuan: Menginisialisasi grafik tren keuangan yang
//         menampilkan perbandingan pendapatan vs pengeluaran.
//
// Tipe grafik dan konfigurasi disesuaikan dengan periode:
//   - 'month' → Line chart, 12 bulan
//   - 'week'  → Bar chart, 7 hari
//   - 'today' → Bar chart, 24 jam
// ============================================================
function initTrendChart() {
    // Cari elemen canvas dengan id="trendChart" di DOM
    const trendCanvas = document.getElementById('trendChart');

    // Jika elemen canvas tidak ditemukan (misalnya user belum login),
    // hentikan eksekusi fungsi ini untuk mencegah error
    if (!trendCanvas) {
        return;
    }

    const chartType = getChartType();
    const isLineChart = chartType === 'line';

    // Buat instance grafik baru menggunakan Chart.js
    new Chart(trendCanvas, {
        type: chartType, // Tipe grafik disesuaikan dengan periode

        data: {
            labels  : trendLabels, // Label sumbu X sesuai periode

            datasets: [
                // Dataset 1: Pendapatan
                {
                    label          : 'Pendapatan',
                    borderColor    : '#4f46e5',                  // Warna garis/border: biru indigo
                    backgroundColor: isLineChart
                        ? 'rgba(79,70,229,.12)'                  // Line: area transparan
                        : 'rgba(79,70,229,.75)',                 // Bar: warna solid
                    data           : revenueData,
                    fill           : isLineChart,                // Isi area hanya untuk line chart
                    tension        : isLineChart ? 0.4 : 0,      // Kelengkungan hanya untuk line
                    borderWidth    : isLineChart ? 2 : 0,
                    borderRadius   : isLineChart ? 0 : 6,        // Sudut bulat untuk bar
                    pointRadius    : isLineChart ? 3 : 0,
                    pointBorderWidth    : isLineChart ? 2 : 0,
                    pointBackgroundColor: '#fff',
                    pointBorderColor    : '#4f46e5'
                },

                // Dataset 2: Pengeluaran
                {
                    label          : 'Pengeluaran',
                    borderColor    : '#ef4444',                  // Warna garis/border: merah
                    backgroundColor: isLineChart
                        ? 'rgba(239,68,68,.08)'                  // Line: area sangat transparan
                        : 'rgba(239,68,68,.65)',                 // Bar: warna solid
                    data           : expenseData,
                    fill           : isLineChart,
                    tension        : isLineChart ? 0.4 : 0,
                    borderWidth    : isLineChart ? 2 : 0,
                    borderRadius   : isLineChart ? 0 : 6,
                    pointRadius    : isLineChart ? 3 : 0,
                    pointBorderWidth    : isLineChart ? 2 : 0,
                    pointBackgroundColor: '#fff',
                    pointBorderColor    : '#ef4444'
                }
            ]
        },

        // Gunakan konfigurasi default yang sudah didefinisikan di createChartDefaults()
        options: createChartDefaults()
    });
}

// ============================================================
// Fungsi: initDashboard
// Tujuan: Entry point utama — memanggil semua fungsi inisialisasi
//         yang perlu dijalankan saat halaman dashboard dimuat.
// ============================================================
function initDashboard() {
    animateFadeUp();   // Jalankan animasi fade-up pada elemen-elemen halaman
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
