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
use Illuminate\Support\Facades\DB;
use App\Support\PeriodFilter;

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
        $search = $request->input('search', '');
        $metode = $request->input('metode', '');

        $now = Carbon::now();
        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolveFromRequest($request, $now);

        // ----------------------------------------------------------
        // Query dasar: ambil semua penjualan dalam rentang periode terpilih
        // ----------------------------------------------------------
        $query = PeriodFilter::apply(Sale::with('user'), 'transaction_date', $periodStart, $periodEnd);

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
        $periodSales = fn () => PeriodFilter::apply(Sale::query(), 'transaction_date', $periodStart, $periodEnd);
        $totalSubtotal = $periodSales()->sum('subtotal');

        // Header sale menyimpan nilai yang benar-benar diterapkan saat transaksi.
        // Data historis yang kosong tidak diestimasi dari tarif produk terkini.
        $totalDiskon = $periodSales()->sum('discount');
        $totalPajak = $periodSales()->sum('tax');
        $hasIncompleteItemFinancials = $periodSales()
            ->where(function ($query) {
                $query->whereDoesntHave('saleItems')
                    ->orWhereHas('saleItems', fn ($items) => $items->whereNull('discount')->orWhereNull('tax'));
            })
            ->exists();
        $hasUnreconciledFinancials = $periodSales()
            ->whereRaw('ABS(subtotal - discount + tax - total) > 0.005')
            ->exists();
        $totalBersih = $periodSales()->sum('total');
        $jumlahTransaksi = $periodSales()->count();
        $rataRataTransaksi = $jumlahTransaksi > 0 ? $totalBersih / $jumlahTransaksi : 0;

        // ============================================================
        // PERSENTASE PERUBAHAN vs PERIODE SEBELUMNYA
        // ============================================================
        if ($period === 'all') {
            $periodLaluStart = $periodLaluEnd = null;
        } elseif ($period === 'today') {
            $periodLaluStart = $now->copy()->subDay()->startOfDay();
            $periodLaluEnd = $now->copy()->subDay()->endOfDay();
        } elseif ($period === 'week') {
            $periodLaluStart = $now->copy()->subWeek()->startOfWeek();
            $periodLaluEnd = $now->copy()->subWeek()->endOfWeek();
        } else {
            $periodLaluStart = $now->copy()->subMonth()->startOfMonth();
            $periodLaluEnd = $now->copy()->subMonth()->endOfMonth();
        }

        $totalPeriodLalu = $periodLaluStart
            ? Sale::whereBetween('transaction_date', [$periodLaluStart, $periodLaluEnd])->sum('total')
            : null;

        $persentasePerubahan = $totalPeriodLalu !== null && $totalPeriodLalu > 0
            ? (($totalBersih - $totalPeriodLalu) / $totalPeriodLalu) * 100
            : ($totalPeriodLalu !== null && $totalBersih > 0 ? 100 : 0);

        // ============================================================
        // DATA GRAFIK — 10 produk dengan nilai penjualan terbesar
        // ============================================================
        $topSellingProducts = PeriodFilter::apply(
            DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.store_id', $request->user()->store_id)
                ->where('products.store_id', $request->user()->store_id),
            'sales.transaction_date',
            $periodStart,
            $periodEnd
        )
            ->selectRaw('products.id, products.name, SUM(sale_items.subtotal - COALESCE(sale_items.discount, 0)) as sales_value, SUM(sale_items.quantity) as units_sold')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sales_value')
            ->orderBy('products.name')
            ->limit(10)
            ->get();
        $topProductLabels = $topSellingProducts->pluck('name')->all();
        $topProductValues = $topSellingProducts->map(fn ($product) => (float) $product->sales_value)->all();
        $topProductUnits = $topSellingProducts->map(fn ($product) => (int) $product->units_sold)->all();

        // ============================================================
        // DATA PIE CHART — Distribusi metode pembayaran
        // ============================================================
        $distribusiMetode = PeriodFilter::apply(
            Sale::selectRaw('payment_method, SUM(total) as total, COUNT(*) as jumlah'),
            'transaction_date',
            $periodStart,
            $periodEnd
        )
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
            'hasIncompleteItemFinancials',
            'hasUnreconciledFinancials',
            'totalBersih',
            'jumlahTransaksi',
            'rataRataTransaksi',
            'persentasePerubahan',
            'totalPeriodLalu',
            'topProductLabels',
            'topProductValues',
            'topProductUnits',
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
        $sale->load(['user', 'saleItems.product']);
        $hasItemFinancialSnapshots = $sale->saleItems->isNotEmpty()
            && $sale->saleItems->every(fn ($item) => $item->discount !== null && $item->tax !== null);
        $headerTotalsReconcile = abs(
            (float) $sale->subtotal - (float) $sale->discount + (float) $sale->tax - (float) $sale->total
        ) < 0.005;

        // Tampilkan view detail dengan data penjualan
        return view('pemasukan.show', compact('sale', 'hasItemFinancialSnapshots', 'headerTotalsReconcile'));
    }
}
