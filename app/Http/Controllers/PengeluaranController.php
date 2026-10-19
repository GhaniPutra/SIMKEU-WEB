<?php

namespace App\Http\Controllers;

// ============================================================
// Import class-class yang dibutuhkan controller ini
// ============================================================
use Illuminate\Http\Request;          // Untuk membaca data dari request (query string, form)
use Illuminate\Support\Facades\Auth;  // Untuk mendapatkan user yang sedang login
use Carbon\Carbon;                    // Untuk manipulasi dan format tanggal/waktu
use App\Models\Expense;               // Model pengeluaran operasional
use App\Support\PeriodFilter;

class PengeluaranController extends Controller
{
    // ============================================================
    // DAFTAR KATEGORI PENGELUARAN
    // Dicocokkan dengan nilai enum di kolom 'category' tabel expenses
    // ============================================================
    private const KATEGORI_LABEL = [
        'beli_stok'  => ['label' => 'Beli Stok',   'icon' => 'fa-boxes-stacking', 'warna' => '#f62440'],
        'gaji'       => ['label' => 'Gaji',         'icon' => 'fa-user-tie',       'warna' => '#419623'],
        'listrik'    => ['label' => 'Listrik',      'icon' => 'fa-bolt',           'warna' => '#c5a102'],
        'air'        => ['label' => 'Air',           'icon' => 'fa-droplet',        'warna' => '#ff2c48'],
        'sewa'       => ['label' => 'Sewa',         'icon' => 'fa-building',       'warna' => '#f62440'],
        'transport'  => ['label' => 'Transport',    'icon' => 'fa-truck',          'warna' => '#cf0621'],
        'lainnya'    => ['label' => 'Lainnya',       'icon' => 'fa-ellipsis',       'warna' => '#aa0a1f'],
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
        $search    = $request->input('search', '');
        $kategori  = $request->input('kategori', '');

        $now = Carbon::now();

        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolveFromRequest($request, $now);

        // ----------------------------------------------------------
        // Query dasar: ambil semua pengeluaran dalam rentang periode
        // Sertakan relasi user (yang mencatat)
        // ----------------------------------------------------------
        $periodExpenses = fn () => PeriodFilter::apply(
            Expense::query(),
            'expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        );
        $query = $periodExpenses()->with('user');

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
        $totalBulanIni = $periodExpenses()->sum('amount');
        $jumlahEntri = $periodExpenses()->count();

        $rataRata = $jumlahEntri > 0 ? $totalBulanIni / $jumlahEntri : 0;

        $terbesarAmount = $periodExpenses()->max('amount') ?? 0;

        // ============================================================
        // PERBANDINGAN VS PERIODE SEBELUMNYA
        // ============================================================
        if ($period === 'all') {
            $periodLaluStart = $periodLaluEnd = null;
        } elseif ($period === 'today') {
            $periodLaluStart = $now->copy()->subDay()->startOfDay();
            $periodLaluEnd = $now->copy()->subDay()->endOfDay();
        } elseif ($period === 'week') {
            $previousPeriod = $periodStart->copy()->subWeek();
            $periodLaluStart = $previousPeriod->copy()->startOfWeek();
            // Bandingkan jumlah hari yang sama dalam minggu sebelumnya.
            $elapsedDays = $periodStart->diffInDays($now->copy()->startOfDay());
            $periodLaluEnd = $periodLaluStart->copy()->addDays($elapsedDays)->min($previousPeriod->copy()->endOfWeek());
        } else {
            $previousPeriod = $periodStart->copy()->subMonth();
            $periodLaluStart = $previousPeriod->copy()->startOfMonth();
            // Bulan berjalan dihitung sampai hari ini; samakan jumlah harinya
            // dengan periode lalu dan batasi jika bulan lalu lebih pendek.
            $elapsedDays = $periodStart->diffInDays($now->copy()->startOfDay());
            $periodLaluEnd = $periodLaluStart->copy()->addDays($elapsedDays)->min($previousPeriod->copy()->endOfMonth());
        }

        $totalPeriodLalu = $periodLaluStart
            ? PeriodFilter::apply(Expense::query(), 'expense_date', $periodLaluStart->toDateString(), $periodLaluEnd->toDateString())->sum('amount')
            : null;

        $persentasePerubahan = $totalPeriodLalu !== null && $totalPeriodLalu > 0
            ? (($totalBulanIni - $totalPeriodLalu) / $totalPeriodLalu) * 100
            : ($totalPeriodLalu !== null && $totalBulanIni > 0 ? 100 : 0);

        // ============================================================
        // DATA CHART — Sesuai periode (seperti dashboard)
        // ============================================================
        $chartLabels = [];
        $chartData = [];
        $chartEnd = $periodEnd?->copy()->min($now)->startOfDay();

        if ($period === 'all') {
            $monthlyTotals = Expense::selectRaw('SUBSTR(expense_date, 1, 7) as expense_month, SUM(amount) as amount')
                ->whereDate('expense_date', '<=', $now->toDateString())
                ->groupByRaw('SUBSTR(expense_date, 1, 7)')
                ->orderBy('expense_month')
                ->pluck('amount', 'expense_month');

            foreach ($monthlyTotals as $month => $total) {
                $chartLabels[] = Carbon::createFromFormat('!Y-m', $month)->format('m/Y');
                $chartData[] = (float) $total;
            }
        } elseif ($period === 'today') {
            $chartLabels[] = 'Hari ini';
            $chartData[] = (float) Expense::whereDate('expense_date', $now->toDateString())->sum('amount');
        } else {
            $dailyTotals = PeriodFilter::apply(
                Expense::query(),
                'expense_date',
                $periodStart?->toDateString(),
                $chartEnd?->toDateString()
            )
                ->selectRaw('expense_date, SUM(amount) as amount')
                ->groupBy('expense_date')
                ->orderBy('expense_date')
                ->pluck('amount', 'expense_date');
        }

        if (in_array($period, ['week', 'month'], true)) {
            for ($date = $periodStart->copy()->startOfDay(); $chartEnd && $date->lte($chartEnd); $date->addDay()) {
                $key = $date->toDateString();
                $chartLabels[] = $period === 'week' ? $date->translatedFormat('D') : $date->format('d/m');
                $chartData[] = (float) ($dailyTotals[$key] ?? 0);
            }
        }

        // ============================================================
        // PERBANDINGAN PER KATEGORI — gunakan rentang berjalan sampai hari ini
        // agar dibandingkan dengan rentang yang sama pada periode sebelumnya.
        // Untuk semua periode, hanya nominal aktif yang tersedia.
        // ============================================================
        $comparisonCurrentEnd = $periodEnd?->copy()->min($now)->startOfDay();
        $currentKategoriTotals = PeriodFilter::apply(
            Expense::query(),
            'expense_date',
            $periodStart?->toDateString(),
            $comparisonCurrentEnd?->toDateString()
        )
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->keyBy('category'); // Berindeks nama kategori

        $previousKategoriTotals = $periodLaluStart
            ? PeriodFilter::apply(
                Expense::query(),
                'expense_date',
                $periodLaluStart->toDateString(),
                $periodLaluEnd->toDateString()
            )
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get()
                ->keyBy('category')
            : collect();

        $perbandinganKategori = [];
        foreach (self::KATEGORI_LABEL as $key => $info) {
            $current = (float) ($currentKategoriTotals[$key]->total ?? 0);
            $previous = $periodLaluStart
                ? (float) ($previousKategoriTotals[$key]->total ?? 0)
                : null;
            $difference = $previous !== null ? $current - $previous : null;

            // Jika nilai pembanding nol, persentase tidak terdefinisi.
            // Tampilkan status "Baru" untuk kenaikan dari nol tanpa membagi nol.
            $percentage = $previous !== null && $previous > 0
                ? ($difference / $previous) * 100
                : ($previous === 0.0 && $current === 0.0 ? 0 : null);

            if ($previous === null) {
                [$status, $statusIcon, $statusClass] = ['Tidak tersedia', 'fa-minus', 'neutral'];
            } elseif ($previous === 0.0 && $current > 0) {
                [$status, $statusIcon, $statusClass] = ['Baru', 'fa-circle-plus', 'up'];
            } elseif ($difference > 0) {
                [$status, $statusIcon, $statusClass] = ['Naik', 'fa-arrow-up', 'up'];
            } elseif ($difference < 0) {
                [$status, $statusIcon, $statusClass] = ['Turun', 'fa-arrow-down', 'down'];
            } else {
                [$status, $statusIcon, $statusClass] = ['Tetap', 'fa-minus', 'neutral'];
            }

            $perbandinganKategori[] = [
                'key' => $key,
                'label' => $info['label'],
                'icon' => $info['icon'],
                'warna' => $info['warna'],
                'current' => $current,
                'previous' => $previous,
                'difference' => $difference,
                'percentage' => $percentage,
                'status' => $status,
                'statusIcon' => $statusIcon,
                'statusClass' => $statusClass,
            ];
        }

        // Tampilkan kategori dengan pengeluaran periode berjalan terbesar lebih dahulu.
        usort($perbandinganKategori, fn ($a, $b) =>
            ($b['current'] <=> $a['current']) ?: (($b['previous'] ?? 0) <=> ($a['previous'] ?? 0))
        );

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
            'periodStart',           // Awal periode aktif untuk keterangan perbandingan
            'search',                // Kata kunci pencarian
            'kategori',              // Kategori aktif filter
            'totalBulanIni',         // Total nominal semua pengeluaran dalam periode
            'jumlahEntri',           // Jumlah baris pengeluaran dalam periode
            'rataRata',              // Rata-rata nilai per entry
            'terbesarAmount',        // Nilai pengeluaran terbesar
            'totalPeriodLalu',       // Total bulan lalu untuk pembanding
            'persentasePerubahan',   // Persentase naik/turun vs bulan lalu
            'chartLabels',           // Label chart tren harian
            'chartData',             // Data chart tren harian
            'comparisonCurrentEnd',  // Batas tanggal berjalan untuk perbandingan kategori
            'periodLaluStart',       // Awal rentang periode pembanding
            'periodLaluEnd',         // Akhir rentang periode pembanding
            'perbandinganKategori',  // Nominal dan perubahan per kategori
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
        return redirect()->route($request->user()->hasPermission('expenses.view') ? 'pengeluaran.index' : 'pengeluaran.create')
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
