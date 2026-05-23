<?php

namespace App\Http\Controllers;

// ============================================================
// Import class-class yang dibutuhkan controller ini
// ============================================================
use Illuminate\Http\Request;          // Untuk membaca data dari request (query string, form)
use Illuminate\Support\Facades\Auth;  // Untuk mendapatkan user yang sedang login
use Carbon\Carbon;                    // Untuk manipulasi dan format tanggal/waktu
use App\Models\Expense;               // Model pengeluaran operasional

class PengeluaranController extends Controller
{
    // ============================================================
    // DAFTAR KATEGORI PENGELUARAN
    // Dicocokkan dengan nilai enum di kolom 'category' tabel expenses
    // ============================================================
    private const KATEGORI_LABEL = [
        'beli_stok'  => ['label' => 'Beli Stok',   'icon' => 'fa-boxes-stacking', 'warna' => '#4f46e5'],
        'gaji'       => ['label' => 'Gaji',         'icon' => 'fa-user-tie',       'warna' => '#06b6d4'],
        'listrik'    => ['label' => 'Listrik',      'icon' => 'fa-bolt',           'warna' => '#f59e0b'],
        'air'        => ['label' => 'Air',           'icon' => 'fa-droplet',        'warna' => '#3b82f6'],
        'sewa'       => ['label' => 'Sewa',         'icon' => 'fa-building',       'warna' => '#8b5cf6'],
        'transport'  => ['label' => 'Transport',    'icon' => 'fa-truck',          'warna' => '#10b981'],
        'lainnya'    => ['label' => 'Lainnya',      'icon' => 'fa-ellipsis',       'warna' => '#94a3b8'],
    ];

    // ============================================================
    // INDEX — Menampilkan daftar pengeluaran + statistik
    // GET /pengeluaran
    // ============================================================
    public function index(Request $request)
    {
        // ----------------------------------------------------------
        // Ambil parameter filter dari URL query string
        // Default: 'month'
        // ----------------------------------------------------------
        $period    = $request->input('period', 'month');
        $search    = $request->input('search', '');
        $kategori  = $request->input('kategori', '');

        $now = Carbon::now();

        // Tentukan rentang tanggal dan label berdasarkan period
        if ($period === 'today') {
            $periodStart = $now->copy()->startOfDay();
            $periodEnd = $now->copy()->endOfDay();
            $periodLabel = 'Hari Ini';
        } elseif ($period === 'week') {
            $periodStart = $now->copy()->startOfWeek();
            $periodEnd = $now->copy()->endOfWeek();
            $periodLabel = 'Minggu Ini';
        } else {
            $period = 'month';
            $periodStart = $now->copy()->startOfMonth();
            $periodEnd = $now->copy()->endOfMonth();
            $periodLabel = 'Bulan Ini';
        }

        // ----------------------------------------------------------
        // Query dasar: ambil semua pengeluaran dalam rentang periode
        // Sertakan relasi user (yang mencatat)
        // ----------------------------------------------------------
        $query = Expense::with('user')
            ->whereBetween('expense_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);

        // Filter pencarian pada kolom deskripsi
        if ($search !== '') {
            $query->where('description', 'like', "%{$search}%");
        }

        // Filter berdasarkan kategori pengeluaran (jika dipilih)
        if ($kategori !== '') {
            $query->where('category', $kategori);
        }

        // Ambil hasil, urutkan dari terbaru, paginasi 15 per halaman
        $pengeluaranList = $query->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        // ============================================================
        // STATISTIK KESELURUHAN PERIODE TERPILIH
        // ============================================================
        $totalBulanIni = Expense::whereBetween('expense_date', [
            $periodStart->toDateString(),
            $periodEnd->toDateString(),
        ])->sum('amount');

        $jumlahEntri = Expense::whereBetween('expense_date', [
            $periodStart->toDateString(),
            $periodEnd->toDateString(),
        ])->count();

        $rataRata = $jumlahEntri > 0 ? $totalBulanIni / $jumlahEntri : 0;

        $terbesarAmount = Expense::whereBetween('expense_date', [
            $periodStart->toDateString(),
            $periodEnd->toDateString(),
        ])->max('amount') ?? 0;

        // ============================================================
        // PERBANDINGAN VS PERIODE SEBELUMNYA
        // ============================================================
        if ($period === 'today') {
            $periodLaluStart = $now->copy()->subDay()->startOfDay();
            $periodLaluEnd = $now->copy()->subDay()->endOfDay();
        } elseif ($period === 'week') {
            $periodLaluStart = $now->copy()->subWeek()->startOfWeek();
            $periodLaluEnd = $now->copy()->subWeek()->endOfWeek();
        } else {
            $periodLaluStart = $now->copy()->subMonth()->startOfMonth();
            $periodLaluEnd = $now->copy()->subMonth()->endOfMonth();
        }

        $totalPeriodLalu = Expense::whereBetween('expense_date', [
            $periodLaluStart->toDateString(),
            $periodLaluEnd->toDateString(),
        ])->sum('amount');

        $persentasePerubahan = $totalPeriodLalu > 0
            ? (($totalBulanIni - $totalPeriodLalu) / $totalPeriodLalu) * 100
            : ($totalBulanIni > 0 ? 100 : 0);

        // ============================================================
        // DATA CHART — Sesuai periode (seperti dashboard)
        // ============================================================
        $chartLabels = [];
        $chartData = [];

        if ($period === 'month') {
            // 12 bulan (Jan-Des tahun ini)
            $trendQuery = Expense::selectRaw('MONTH(expense_date) as m, SUM(amount) as amount')
                ->whereYear('expense_date', $now->year)
                ->groupBy('m')
                ->orderBy('m')
                ->pluck('amount', 'm');

            $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $chartLabels[] = $namaBulan[$m - 1];
                $chartData[] = (float) ($trendQuery[$m] ?? 0);
            }
        } elseif ($period === 'week') {
            // 7 hari (Senin-Minggu minggu ini)
            $weekStart = $now->copy()->startOfWeek();
            $trendQuery = Expense::selectRaw('expense_date as date, SUM(amount) as amount')
                ->where('expense_date', '>=', $weekStart->toDateString())
                ->where('expense_date', '<=', $now->copy()->endOfWeek()->toDateString())
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('amount', 'date');

            $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            for ($d = 0; $d < 7; $d++) {
                $date = $weekStart->copy()->addDays($d)->toDateString();
                $chartLabels[] = $namaHari[$d];
                $chartData[] = (float) ($trendQuery[$date] ?? 0);
            }
        } else {
            // 24 jam (00:00-23:00 hari ini)
            // Karena expense_date saat ini tipe DATE, semua data ada di hari tersebut tanpa jam.
            // Kita tampilkan seluruh jumlah hari di jam 00:00.
            $dayStart = $now->copy()->startOfDay();
            $totalToday = Expense::where('expense_date', $dayStart->toDateString())->sum('amount');

            for ($h = 0; $h < 24; $h++) {
                $chartLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                // Masukkan ke jam 00 saja karena tidak punya data waktu detail
                $chartData[] = $h === 0 ? (float) $totalToday : 0;
            }
        }

        // ============================================================
        // DATA CHART — Distribusi per kategori (bar chart)
        // ============================================================
        $distribusiKategori = Expense::selectRaw('category, SUM(amount) as total, COUNT(*) as jumlah')
            ->whereBetween('expense_date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->groupBy('category')
            ->get()
            ->keyBy('category'); // Berindeks nama kategori

        // Susun label, data, dan warna untuk chart kategori
        $kategoriChartLabels = [];
        $kategoriChartData   = [];
        $kategoriChartColors = [];

        foreach (self::KATEGORI_LABEL as $key => $info) {
            $kategoriChartLabels[] = $info['label'];                                   // Nama kategori (Indonesia)
            $kategoriChartData[]   = (float) ($distribusiKategori[$key]->total ?? 0); // Total nominal
            $kategoriChartColors[] = $info['warna'];                                   // Warna bar
        }

        // ============================================================
        // RINGKASAN PER KATEGORI untuk tabel breakdown
        // ============================================================
        $ringkasanKategori = [];
        foreach (self::KATEGORI_LABEL as $key => $info) {
            $total  = (float) ($distribusiKategori[$key]->total  ?? 0);
            $jumlah = (int)   ($distribusiKategori[$key]->jumlah ?? 0);
            if ($total > 0) {
                // Hitung persentase dari total bulan ini (hindari pembagian nol)
                $persen = $totalBulanIni > 0 ? ($total / $totalBulanIni) * 100 : 0;
                $ringkasanKategori[] = [
                    'key'    => $key,
                    'label'  => $info['label'],
                    'icon'   => $info['icon'],
                    'warna'  => $info['warna'],
                    'total'  => $total,
                    'jumlah' => $jumlah,
                    'persen' => $persen,
                ];
            }
        }

        // Urutkan kategori dari yang terbesar nominalnya
        usort($ringkasanKategori, fn($a, $b) => $b['total'] <=> $a['total']);

        // ============================================================
        // Siapkan mapping label kategori sebagai variabel lokal
        // agar bisa diikutsertakan di compact() dan ditampilkan di view
        // Dipakai oleh dropdown filter dan badge kategori di tabel
        // ============================================================
        $kategoriLabel = self::KATEGORI_LABEL;

        // ============================================================
        // Kirim semua data ke view pengeluaran/index.blade.php
        // ============================================================
        return view('pengeluaran.index', compact(
            'pengeluaranList',       // Koleksi paginasi pengeluaran
            'period',                // Periode aktif filter
            'periodLabel',           // Label periode aktif filter
            'search',                // Kata kunci pencarian
            'kategori',              // Kategori aktif filter
            'totalBulanIni',         // Total nominal semua pengeluaran bulan ini
            'jumlahEntri',           // Jumlah baris pengeluaran bulan ini
            'rataRata',              // Rata-rata nilai per entry
            'terbesarAmount',        // Nilai pengeluaran terbesar
            'totalPeriodLalu',       // Total bulan lalu untuk pembanding
            'persentasePerubahan',   // Persentase naik/turun vs bulan lalu
            'chartLabels',           // Label chart tren harian
            'chartData',             // Data chart tren harian
            'kategoriChartLabels',   // Label chart kategori
            'kategoriChartData',     // Data chart kategori
            'kategoriChartColors',   // Warna chart kategori
            'ringkasanKategori',     // Ringkasan breakdown per kategori
            'kategoriLabel'          // Mapping kategori (key → label/icon/warna)
        ));
    }

    // ============================================================
    // SHOW — Menampilkan detail satu entri pengeluaran
    // GET /pengeluaran/{id}
    // ============================================================
    public function show(Expense $expense)
    {
        // Muat relasi user (yang mencatat)
        $expense->load('user');

        // Ambil mapping label kategori untuk ditampilkan di view
        $kategoriLabel = self::KATEGORI_LABEL;

        // Tampilkan view detail pengeluaran
        return view('pengeluaran.show', compact('expense', 'kategoriLabel'));
    }

    // ============================================================
    // CREATE — Menampilkan form input pengeluaran baru
    // GET /pengeluaran/create
    // ============================================================
    public function create()
    {
        // Tampilkan halaman form input pengeluaran baru
        return view('pengeluaran.create');
    }

    // ============================================================
    // STORE — Simpan data pengeluaran baru ke database
    // POST /pengeluaran
    // ============================================================
    public function store(Request $request)
    {
        // Validasi input form pengeluaran dengan aturan ketat
        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'category'     => ['required', 'in:beli_stok,gaji,listrik,air,sewa,transport,lainnya'],
            'description'  => ['nullable', 'string', 'max:500'],
        ]);

        // Simpan entri pengeluaran baru dengan relasi user yang login
        Expense::create([
            'user_id'      => Auth::id(),
            'expense_date' => $validated['expense_date'],
            'amount'       => $validated['amount'],
            'category'     => $validated['category'],
            'description'  => $validated['description'] ?? null,
        ]);

        // Redirect kembali ke halaman daftar pengeluaran dengan pesan sukses
        return redirect()->route('pengeluaran.index')
            ->with('success', 'Pengeluaran baru berhasil disimpan.');
    }

    // ============================================================
    // Helper publik — Kembalikan mapping label kategori
    // Bisa dipanggil dari manapun jika diperlukan
    // ============================================================
    public static function getKategoriLabel(): array
    {
        return self::KATEGORI_LABEL;
    }
}
