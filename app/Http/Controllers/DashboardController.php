<?php

namespace App\Http\Controllers;

// ====================================
// Import kelas yang dibutuhkan
// ====================================
use Illuminate\Http\Request;             // Untuk menerima query parameter dari request HTTP
use Illuminate\Support\Facades\Auth;     // Untuk mengakses informasi user yang sedang login
use Carbon\Carbon;                       // Library untuk manipulasi tanggal dan waktu
use App\Models\Sale;                     // Model untuk data penjualan / pemasukan
use App\Models\Expense;                  // Model untuk data pengeluaran operasional
use App\Models\Product;                  // Model untuk data produk / stok
use App\Models\User;                     // Model untuk data user / pegawai

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama SIMKEU.
     *
     * Method ini mendukung filter periode melalui query parameter:
     *   ?period=today   → Data hari ini saja (grafik: per jam 00–23)
     *   ?period=week    → Data 7 hari terakhir (grafik: Senin–Minggu)
     *   ?period=month   → Data bulan berjalan (grafik: 12 bulan Jan–Des)
     *
     * GET /dashboard?period=today|week|month
     *
     * @param  \Illuminate\Http\Request  $request  Objek request HTTP dari browser
     * @return \Illuminate\View\View               View halaman dashboard dengan semua data
     */
    public function index(Request $request)
    {
        // =====================================================
        // Ambil data user yang sedang login
        // =====================================================
        $user = Auth::user(); // Objek user yang sedang terautentikasi

        // Store tidak dipakai karena semua pengguna adalah admin utama
        $store = null;

        // =====================================================
        // Baca parameter period dari URL query string
        // Jika tidak ada, gunakan 'month' sebagai default
        // Contoh URL: /dashboard?period=today
        // =====================================================
        $period = $request->query('period', 'month'); // Ambil nilai period, default 'month'

        // =====================================================
        // Tentukan rentang tanggal berdasarkan periode yang dipilih
        // Variabel $periodStart akan digunakan sebagai titik awal filter
        // =====================================================
        $now = Carbon::now(); // Waktu saat ini

        if ($period === 'today') {
            // Periode: Hari ini — mulai dari 00:00:00 hari ini
            $periodStart = $now->copy()->startOfDay();
            $periodLabel = 'Hari Ini'; // Label untuk ditampilkan di view
        } elseif ($period === 'week') {
            // Periode: Minggu ini — mulai dari Senin minggu berjalan
            $periodStart = $now->copy()->startOfWeek();
            $periodLabel = 'Minggu Ini';
        } else {
            // Periode: Bulan ini (default) — mulai dari tanggal 1 bulan berjalan
            $period = 'month'; // Normalisasi nilai agar konsisten
            $periodStart = $now->copy()->startOfMonth();
            $periodLabel = 'Bulan Ini';
        }

        // =====================================================
        // Hitung Pendapatan (Revenue) berdasarkan periode
        // =====================================================

        // Pendapatan hari ini (selalu dihitung dari awal hari, tidak terpengaruh filter)
        $totalRevenueToday = Sale::where('transaction_date', '>=', $now->copy()->startOfDay())
            ->sum('total'); // Jumlahkan kolom 'total' dari semua transaksi hari ini

        // Pendapatan pada periode yang dipilih (today / week / month)
        $totalRevenueMonth = Sale::where('transaction_date', '>=', $periodStart)
            ->sum('total'); // Jumlahkan kolom 'total' dari semua transaksi dalam periode

        // =====================================================
        // Hitung Pengeluaran (Expense) berdasarkan periode
        // =====================================================

        // Pengeluaran hari ini (selalu dihitung, tidak terpengaruh filter)
        $totalExpenseToday = Expense::where('expense_date', '>=', $now->copy()->startOfDay()->toDateString())
            ->sum('amount'); // Jumlahkan kolom 'amount' dari semua pengeluaran hari ini

        // Pengeluaran pada periode yang dipilih
        $totalExpenseMonth = Expense::where('expense_date', '>=', $periodStart->toDateString())
            ->sum('amount'); // Jumlahkan kolom 'amount' dari semua pengeluaran dalam periode

        // =====================================================
        // Hitung Laba (Profit) berdasarkan data periode
        // =====================================================

        // Laba kotor: pendapatan dikurangi pengeluaran dalam satu periode
        $grossProfit = $totalRevenueMonth - $totalExpenseMonth;

        // Laba bersih: diasumsikan sama dengan laba kotor (belum ada komponen pajak)
        $netProfit = $grossProfit;

        // =====================================================
        // Hitung Jumlah Transaksi
        // =====================================================

        // Jumlah transaksi hari ini (selalu dihitung dari awal hari)
        $transactionsToday = Sale::where('transaction_date', '>=', $now->copy()->startOfDay())->count();

        // Total seluruh transaksi yang pernah ada di database
        $transactionsTotal = Sale::count();

        // Jumlah transaksi dalam periode yang dipilih (today / week / month)
        $transactionsPeriod = Sale::where('transaction_date', '>=', $periodStart)->count();

        // Jumlah transaksi kemarin (untuk menghitung tren)
        $transactionsYesterday = Sale::whereBetween('transaction_date', [
            $now->copy()->subDay()->startOfDay(),
            $now->copy()->subDay()->endOfDay(),
        ])->count();

        // Hitung persentase tren transaksi hari ini vs kemarin
        $transactionsTrendPercent = $transactionsYesterday > 0
            ? (($transactionsToday - $transactionsYesterday) / $transactionsYesterday) * 100
            : ($transactionsToday > 0 ? 100 : 0);

        // Tentukan label CSS dan teks persentase tren transaksi
        $transactionsTrendLabel = $transactionsTrendPercent >= 0 ? 'trend-up' : 'trend-down';
        $transactionsTrendText  = number_format(abs($transactionsTrendPercent), 1, ',', '.') . '%';

        // =====================================================
        // Hitung Jumlah Pegawai Aktif
        // =====================================================

        // Menghitung semua user yang terdaftar (dianggap sebagai pegawai aktif)
        $activeEmployees = User::count();

        // =====================================================
        // Data Tren untuk Grafik — Berbeda sesuai periode filter
        //
        // 'month' → 12 bulan (Jan–Des tahun ini), total per bulan
        // 'week'  → 7 hari (Senin–Minggu minggu ini), total per hari
        // 'today' → 24 jam (00:00–23:00 hari ini), total per jam
        // =====================================================
        $trendLabels      = [];
        $revenueTrendData = [];
        $expenseTrendData = [];

        if ($period === 'month') {
            // ---- BULANAN: 12 bulan dalam tahun berjalan ----
            $revenueTrendQuery = Sale::whereYear('transaction_date', $now->year)
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => (int) Carbon::parse($sale->transaction_date)->format('n'))
                ->map(fn ($items) => $items->sum('total'));

            $expenseTrendQuery = Expense::whereYear('expense_date', $now->year)
                ->get(['expense_date', 'amount'])
                ->groupBy(fn ($expense) => (int) Carbon::parse($expense->expense_date)->format('n'))
                ->map(fn ($items) => $items->sum('amount'));

            // Nama bulan dalam Bahasa Indonesia
            $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            // Bangun data 12 bulan
            for ($m = 1; $m <= 12; $m++) {
                $trendLabels[]      = $namaBulan[$m - 1];
                $revenueTrendData[] = (float) ($revenueTrendQuery[$m] ?? 0);
                $expenseTrendData[] = (float) ($expenseTrendQuery[$m] ?? 0);
            }
        } elseif ($period === 'week') {
            // ---- MINGGUAN: 7 hari (Senin–Minggu) minggu ini ----
            $weekStart = $now->copy()->startOfWeek(); // Senin minggu ini

            $revenueTrendQuery = Sale::where('transaction_date', '>=', $weekStart)
                ->where('transaction_date', '<=', $now->copy()->endOfWeek())
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => Carbon::parse($sale->transaction_date)->toDateString())
                ->map(fn ($items) => $items->sum('total'));

            $expenseTrendQuery = Expense::where('expense_date', '>=', $weekStart->toDateString())
                ->where('expense_date', '<=', $now->copy()->endOfWeek()->toDateString())
                ->get(['expense_date', 'amount'])
                ->groupBy(fn ($expense) => $expense->expense_date)
                ->map(fn ($items) => $items->sum('amount'));

            // Nama hari dalam Bahasa Indonesia
            $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

            // Bangun data 7 hari
            for ($d = 0; $d < 7; $d++) {
                $date = $weekStart->copy()->addDays($d);
                $key  = $date->toDateString();

                $trendLabels[]      = $namaHari[$d];
                $revenueTrendData[] = (float) ($revenueTrendQuery[$key] ?? 0);
                $expenseTrendData[] = (float) ($expenseTrendQuery[$key] ?? 0);
            }
        } else {
            // ---- HARIAN: 24 jam (00:00–23:00) hari ini ----
            $dayStart = $now->copy()->startOfDay();

            $revenueTrendQuery = Sale::where('transaction_date', '>=', $dayStart)
                ->where('transaction_date', '<=', $now->copy()->endOfDay())
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => (int) Carbon::parse($sale->transaction_date)->format('G'))
                ->map(fn ($items) => $items->sum('total'));

            // Query pengeluaran hari ini berdasarkan kolom tanggal
            $expenseTodayTotal = Expense::where('expense_date', $dayStart->toDateString())
                ->sum('amount');

            // Bangun data 24 jam
            for ($h = 0; $h < 24; $h++) {
                $trendLabels[]      = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                $revenueTrendData[] = (float) ($revenueTrendQuery[$h] ?? 0);
                $expenseTrendData[] = $h === 0 ? (float) $expenseTodayTotal : 0;
            }
        }

        // =====================================================
        // Hitung Persentase Tren (dibandingkan kemarin)
        // Digunakan untuk indikator naik/turun pada KPI card
        // =====================================================

        // Pendapatan kemarin (dari 00:00 sampai 23:59 kemarin)
        $revenueYesterday = Sale::whereBetween('transaction_date', [
            $now->copy()->subDay()->startOfDay(), // Awal kemarin
            $now->copy()->subDay()->endOfDay(),   // Akhir kemarin
        ])->sum('total');

        // Pengeluaran kemarin (berdasarkan tanggal string)
        $expenseYesterday = Expense::where('expense_date', $now->copy()->subDay()->toDateString())
            ->sum('amount');

        // Hitung persentase perubahan pendapatan hari ini vs kemarin
        // Jika kemarin ada data, hitung persentase selisihnya
        // Jika kemarin 0 tapi hari ini ada, anggap naik 100%
        $revenueTrendPercent = $revenueYesterday > 0
            ? (($totalRevenueToday - $revenueYesterday) / $revenueYesterday) * 100
            : ($totalRevenueToday > 0 ? 100 : 0);

        // Hitung persentase perubahan pengeluaran hari ini vs kemarin
        $expenseTrendPercent = $expenseYesterday > 0
            ? (($totalExpenseToday - $expenseYesterday) / $expenseYesterday) * 100
            : ($totalExpenseToday > 0 ? 100 : 0);

        // Tentukan label kelas CSS untuk trend: 'trend-up' atau 'trend-down'
        $revenueTrendLabel  = $revenueTrendPercent >= 0 ? 'trend-up' : 'trend-down';
        $expenseTrendLabel  = $expenseTrendPercent >= 0 ? 'trend-up' : 'trend-down';

        // Format teks persentase untuk ditampilkan (contoh: "12,5%")
        $revenueTrendText   = number_format(abs($revenueTrendPercent), 1, ',', '.') . '%';
        $expenseTrendText   = number_format(abs($expenseTrendPercent), 1, ',', '.') . '%';

        // =====================================================
        // Bangun Daftar Alert / Peringatan Bisnis
        // Ditampilkan sebagai banner di bagian atas dashboard
        // =====================================================
        $alerts = []; // Array untuk menampung semua pesan alert

        // Cek produk dengan stok rendah (≤ 10 unit), ambil maksimal 3 produk
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc') // Urutkan dari stok paling sedikit
            ->take(3)                  // Batasi 3 produk saja
            ->get();

        // Tambahkan alert untuk setiap produk dengan stok rendah
        foreach ($lowStockProducts as $product) {
            $alerts[] = [
                'type'    => 'warning', // Tipe alert: peringatan (kuning)
                'message' => "Stok produk {$product->name} tinggal {$product->stock} {$product->unit}",
            ];
        }

        // Jika pengeluaran melebihi pendapatan dalam periode, tambahkan alert bahaya
        if ($totalExpenseMonth > $totalRevenueMonth) {
            $alerts[] = [
                'type'    => 'danger', // Tipe alert: bahaya (merah)
                'message' => "Pengeluaran {$periodLabel} melebihi pendapatan",
            ];
        }

        // Jika ada transaksi hari ini, tampilkan notifikasi positif
        if ($transactionsToday > 0) {
            $alerts[] = [
                'type'    => 'success', // Tipe alert: sukses (hijau)
                'message' => "{$transactionsToday} transaksi dicatat hari ini",
            ];
        }

        // =====================================================
        // Ambil Aktivitas Terbaru (Penjualan & Pengeluaran)
        // Digunakan untuk daftar aktivitas di sisi kiri dashboard
        // =====================================================

        // Ambil 5 transaksi penjualan terbaru beserta relasi user-nya
        $salesActivities = Sale::with('user')
            ->orderByDesc('transaction_date') // Urutkan dari terbaru
            ->take(5)                          // Batasi 5 data
            ->get()
            ->map(fn ($sale) => [
                'type'    => 'sale', // Tipe aktivitas: penjualan
                // Pesan aktivitas berisi nomor invoice dan nama user yang memproses
                'message' => "Transaksi {$sale->invoice_number} berhasil diproses oleh {$sale->user->name}",
                'date'    => $sale->transaction_date, // Tanggal transaksi (Carbon instance)
            ])
            ->toBase();

        // Ambil 5 entri pengeluaran terbaru beserta relasi user-nya
        $expenseActivities = Expense::with('user')
            ->orderByDesc('expense_date') // Urutkan dari terbaru
            ->take(5)                      // Batasi 5 data
            ->get()
            ->map(fn ($expense) => [
                'type'    => 'expense', // Tipe aktivitas: pengeluaran
                // Pesan aktivitas berisi kategori, jumlah, dan nama user yang mencatat
                'message' => "Pengeluaran {$expense->category} sebesar Rp " . number_format($expense->amount, 0, ',', '.') . " dicatat oleh {$expense->user->name}",
                'date'    => Carbon::parse($expense->expense_date), // Parse ke Carbon agar bisa diffForHumans()
            ])
            ->toBase();

        // Gabungkan aktivitas penjualan dan pengeluaran, urutkan berdasarkan tanggal terbaru, ambil 5 teratas
        $latestActivities = $salesActivities
            ->merge($expenseActivities)   // Gabungkan dua koleksi
            ->sortByDesc('date')          // Urutkan dari terbaru ke terlama
            ->take(5)                     // Ambil 5 aktivitas teratas
            ->values();                   // Reset index array

        // =====================================================
        // Bangun Data Transaksi Terbaru untuk Tabel
        // Menampilkan kombinasi penjualan dan pengeluaran terbaru
        // =====================================================
        $recentTransactions = collect(); // Inisialisasi koleksi kosong

        // Tambahkan 5 penjualan terbaru ke koleksi transaksi
        foreach (Sale::orderByDesc('transaction_date')->take(5)->get() as $sale) {
            $recentTransactions->push([
                'sort_date' => $sale->transaction_date,                  // Untuk pengurutan
                'date'      => $sale->transaction_date->format('d M Y'), // Format tampilan: "26 Apr 2026"
                'type'      => 'revenue',                                 // Tipe untuk pewarnaan badge
                'label'     => 'Pemasukan',                               // Label yang ditampilkan
                'amount'    => $sale->total,                              // Nominal transaksi
                'url'       => route('pemasukan.show', $sale->id),       // Link ke detail pemasukan
            ]);
        }

        // Tambahkan 5 pengeluaran terbaru ke koleksi transaksi
        foreach (Expense::orderByDesc('expense_date')->take(5)->get() as $expense) {
            $recentTransactions->push([
                'sort_date' => Carbon::parse($expense->expense_date),                  // Untuk pengurutan
                'date'      => Carbon::parse($expense->expense_date)->format('d M Y'), // Format tampilan
                'type'      => 'expense',                                               // Tipe untuk pewarnaan badge
                'label'     => 'Pengeluaran',                                          // Label yang ditampilkan
                'amount'    => $expense->amount,                                        // Nominal pengeluaran
                'url'       => route('pengeluaran.show', $expense->id),               // Link ke detail pengeluaran
            ]);
        }

        // Urutkan gabungan transaksi dari terbaru ke terlama, ambil 5 teratas
        $recentTransactions = $recentTransactions
            ->sortByDesc('sort_date') // Urutkan berdasarkan tanggal
            ->take(5)                 // Ambil 5 transaksi teratas
            ->values();               // Reset index array

        // =====================================================
        // Kirim Semua Data ke View Dashboard
        // compact() mengubah nama variabel menjadi array asosiatif
        // =====================================================
        return view('dashboard', compact(
            'user',                // Data user yang sedang login
            'store',               // Data store (null, tidak digunakan)
            'period',              // Periode aktif ('today', 'week', 'month')
            'periodLabel',         // Label periode untuk ditampilkan di UI
            'totalRevenueToday',   // Total pendapatan hari ini
            'totalRevenueMonth',   // Total pendapatan periode yang dipilih
            'totalExpenseToday',   // Total pengeluaran hari ini
            'totalExpenseMonth',   // Total pengeluaran periode yang dipilih
            'grossProfit',         // Laba kotor periode
            'netProfit',           // Laba bersih periode
            'transactionsToday',        // Jumlah transaksi hari ini
            'transactionsTotal',       // Total seluruh transaksi
            'transactionsPeriod',      // Jumlah transaksi dalam periode yang dipilih
            'transactionsTrendLabel',  // Kelas CSS tren transaksi
            'transactionsTrendText',   // Teks persentase tren transaksi
            'activeEmployees',         // Jumlah pegawai aktif
            'trendLabels',         // Label sumbu X grafik tren
            'revenueTrendData',    // Data pendapatan untuk grafik
            'expenseTrendData',    // Data pengeluaran untuk grafik
            'revenueTrendLabel',   // Kelas CSS tren pendapatan
            'revenueTrendText',    // Teks persentase tren pendapatan
            'expenseTrendLabel',   // Kelas CSS tren pengeluaran
            'expenseTrendText',    // Teks persentase tren pengeluaran
            'alerts',              // Daftar pesan alert bisnis
            'latestActivities',    // Daftar aktivitas terbaru
            'recentTransactions'   // Daftar transaksi terbaru untuk tabel
        ));
    }
}
