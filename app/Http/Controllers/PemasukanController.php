<?php

namespace App\Http\Controllers;

// ============================================================
// Import class-class yang dibutuhkan controller ini
// ============================================================
use Illuminate\Http\Request;       // Untuk menerima data dari request (form, query string, dll.)
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan data user yang sedang login
use Carbon\Carbon;                 // Untuk manipulasi dan format tanggal/waktu
use App\Models\Sale;               // Model transaksi penjualan (pemasukan)
use App\Models\SaleItem;          // Model detail item per transaksi
use App\Models\Product;           // Model produk, dipakai untuk dropdown pilih produk
use App\Models\Customer;          // Model pelanggan, dipakai untuk dropdown

class PemasukanController extends Controller
{
    // ============================================================
    // INDEX — Menampilkan daftar semua pemasukan dengan statistik
    // GET /pemasukan
    // ============================================================
    public function index(Request $request)
    {
        // ----------------------------------------------------------
        // Ambil parameter filter period (default: 'month')
        // ----------------------------------------------------------
        $period = $request->input('period', 'month');
        $search = $request->input('search', '');
        $metode = $request->input('metode', '');

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
        // Query dasar: ambil semua penjualan dalam rentang periode terpilih
        // ----------------------------------------------------------
        $query = Sale::with(['user', 'customer'])
            ->whereBetween('transaction_date', [$periodStart, $periodEnd]);

        // Filter pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter metode
        if ($metode !== '') {
            $query->where('payment_method', $metode);
        }

        // Ambil hasil query dengan paginasi
        $penjualanList = $query->orderByDesc('transaction_date')
            ->paginate(15)
            ->appends($request->query());

        // ============================================================
        // STATISTIK PERIODE TERPILIH
        // ============================================================
        $totalSubtotal = Sale::whereBetween('transaction_date', [$periodStart, $periodEnd])->sum('subtotal');
        $totalDiskon = Sale::whereBetween('transaction_date', [$periodStart, $periodEnd])->sum('discount');
        $totalPajak = Sale::whereBetween('transaction_date', [$periodStart, $periodEnd])->sum('tax');
        $totalBersih = Sale::whereBetween('transaction_date', [$periodStart, $periodEnd])->sum('total');
        $jumlahTransaksi = Sale::whereBetween('transaction_date', [$periodStart, $periodEnd])->count();
        $rataRataTransaksi = $jumlahTransaksi > 0 ? $totalBersih / $jumlahTransaksi : 0;

        // ============================================================
        // PERSENTASE PERUBAHAN vs PERIODE SEBELUMNYA
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

        $totalPeriodLalu = Sale::whereBetween('transaction_date', [$periodLaluStart, $periodLaluEnd])->sum('total');

        $persentasePerubahan = $totalPeriodLalu > 0
            ? (($totalBersih - $totalPeriodLalu) / $totalPeriodLalu) * 100
            : ($totalBersih > 0 ? 100 : 0);

        // ============================================================
        // DATA CHART — Sesuai periode (seperti dashboard)
        // ============================================================
        $chartLabels = [];
        $chartData = [];

        if ($period === 'month') {
            // 12 bulan (Jan-Des tahun ini) dengan grouping di PHP untuk kompatibilitas SQLite
            $trendQuery = Sale::whereYear('transaction_date', $now->year)
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => (int) Carbon::parse($sale->transaction_date)->format('n'))
                ->map(fn ($items) => $items->sum('total'));

            $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $chartLabels[] = $namaBulan[$m - 1];
                $chartData[] = (float) ($trendQuery[$m] ?? 0);
            }
        } elseif ($period === 'week') {
            // 7 hari (Senin-Minggu minggu ini)
            $weekStart = $now->copy()->startOfWeek();
            $trendQuery = Sale::where('transaction_date', '>=', $weekStart)
                ->where('transaction_date', '<=', $now->copy()->endOfWeek())
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => Carbon::parse($sale->transaction_date)->toDateString())
                ->map(fn ($items) => $items->sum('total'));

            $namaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            for ($d = 0; $d < 7; $d++) {
                $date = $weekStart->copy()->addDays($d)->toDateString();
                $chartLabels[] = $namaHari[$d];
                $chartData[] = (float) ($trendQuery[$date] ?? 0);
            }
        } else {
            // 24 jam (00:00-23:00 hari ini)
            $dayStart = $now->copy()->startOfDay();
            $trendQuery = Sale::where('transaction_date', '>=', $dayStart)
                ->where('transaction_date', '<=', $now->copy()->endOfDay())
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => (int) Carbon::parse($sale->transaction_date)->format('G'))
                ->map(fn ($items) => $items->sum('total'));

            for ($h = 0; $h < 24; $h++) {
                $chartLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                $chartData[] = (float) ($trendQuery[$h] ?? 0);
            }
        }

        // ============================================================
        // DATA PIE CHART — Distribusi metode pembayaran
        // ============================================================
        $distribusiMetode = Sale::selectRaw('payment_method, SUM(total) as total, COUNT(*) as jumlah')
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $metodeLabels = ['Tunai', 'Transfer', 'QRIS'];
        $metodeData = [
            (float) ($distribusiMetode['tunai']->total ?? 0),
            (float) ($distribusiMetode['transfer']->total ?? 0),
            (float) ($distribusiMetode['qris']->total ?? 0),
        ];

        // ============================================================
        // Kirim semua data ke view pemasukan/index.blade.php
        // ============================================================
        return view('pemasukan.index', compact(
            'penjualanList',
            'period',
            'periodLabel',
            'search',
            'metode',
            'totalSubtotal',
            'totalDiskon',
            'totalPajak',
            'totalBersih',
            'jumlahTransaksi',
            'rataRataTransaksi',
            'persentasePerubahan',
            'totalPeriodLalu',
            'chartLabels',
            'chartData',
            'metodeLabels',
            'metodeData'
        ));
    }

    // ============================================================
    // SHOW — Menampilkan detail satu transaksi beserta item-itemnya
    // GET /pemasukan/{id}
    // ============================================================
    public function show(Sale $sale)
    {
        // Muat relasi: user (kasir), customer (pelanggan),
        // saleItems beserta produknya masing-masing
        $sale->load(['user', 'customer', 'saleItems.product']);

        // Tampilkan view detail dengan data penjualan
        return view('pemasukan.show', compact('sale'));
    }
}
