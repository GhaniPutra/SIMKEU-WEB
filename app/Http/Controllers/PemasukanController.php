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
        $period = $request->query('period', 'month');
        $search = $request->input('search', '');
        $metode = $request->input('metode', '');

        $now = Carbon::now();
        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolve($period, $now);

        // ----------------------------------------------------------
        // Query dasar: ambil semua penjualan dalam rentang periode terpilih
        // ----------------------------------------------------------
        $query = PeriodFilter::apply(Sale::with(['user', 'customer']), 'transaction_date', $periodStart, $periodEnd);

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

        // ---------- Perhitungan diskon dan pajak yang lebih akurat ----------
        // Beberapa transaksi mungkin tidak menyimpan breakdown per-item dengan
        // konsisten di kolom sales.discount / sales.tax. Untuk memastikan KPI
        // merefleksikan perhitungan nyata (sama seperti POS), hitung ulang dari
        // sale_items bergabung dengan products menggunakan tax_rate & discount_rate.

        // Total diskon produk (dari rate produk) = SUM(price_at_sale * qty * discount_rate/100)
        $totalProductDiscount = (float) PeriodFilter::apply(DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id'), 'sales.transaction_date', $periodStart, $periodEnd)
            ->selectRaw('COALESCE(SUM(sale_items.price_at_sale * sale_items.quantity * (products.discount_rate / 100.0)),0) as total')
            ->value('total');

        // Total pajak dihitung setelah diskon produk per item:
        // SUM( (price * qty - price*qty*discount_rate/100) * (tax_rate/100) )
        $totalProductTax = (float) PeriodFilter::apply(DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id'), 'sales.transaction_date', $periodStart, $periodEnd)
            ->selectRaw('COALESCE(SUM((sale_items.price_at_sale * sale_items.quantity - (sale_items.price_at_sale * sale_items.quantity * (products.discount_rate / 100.0))) * (products.tax_rate / 100.0)),0) as total')
            ->value('total');

        // Beberapa transaksi menyimpan diskon tambahan (extra discount) di kolom
        // sales.discount (total diskon produk + extra). Kita ambil total dari
        // kolom sales.discount sebagai fallback/penyesuaian.
        $sumSaleDiscounts = (float) $periodSales()->sum('discount');
        $sumSaleTax = (float) $periodSales()->sum('tax');

        // Jika kolom sales.discount terisi (mis. import lama), gunakan nilainya.
        // Jika tidak (atau nol), gunakan hasil perhitungan berdasarkan item.
        // Untuk diskon: prioritaskan nilai dari DB jika > 0, tapi tetap pastikan
        // minimal sama dengan totalProductDiscount.
        if ($sumSaleDiscounts > 0) {
            $totalDiskon = $sumSaleDiscounts;
        } else {
            $totalDiskon = $totalProductDiscount;
        }

        // Untuk pajak: gunakan nilai di kolom sales.tax bila tersedia, selain itu
        // gunakan perhitungan berbasis produk.
        $totalPajak = $sumSaleTax > 0 ? $sumSaleTax : $totalProductTax;
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
        // DATA CHART — Sesuai periode (seperti dashboard)
        // ============================================================
        $chartLabels = [];
        $chartData = [];

        if ($period === 'all') {
            $monthlyTotals = DB::table('sales')
                ->selectRaw('SUBSTR(transaction_date, 1, 7) as sale_month, SUM(total) as total')
                ->groupByRaw('SUBSTR(transaction_date, 1, 7)')
                ->orderBy('sale_month')
                ->pluck('total', 'sale_month');

            foreach ($monthlyTotals as $month => $total) {
                $chartLabels[] = Carbon::createFromFormat('!Y-m', $month)->format('m/Y');
                $chartData[] = (float) $total;
            }
        } elseif ($period === 'today') {
            $hourlyTotals = $periodSales()
                ->get(['transaction_date', 'total'])
                ->groupBy(fn ($sale) => Carbon::parse($sale->transaction_date)->format('G'))
                ->map(fn ($sales) => $sales->sum('total'));

            for ($hour = 0; $hour < 24; $hour++) {
                $chartLabels[] = str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00';
                $chartData[] = (float) ($hourlyTotals[$hour] ?? 0);
            }
        } else {
            $dailyTotals = $periodSales()
                ->selectRaw('DATE(transaction_date) as sale_date, SUM(total) as total')
                ->groupByRaw('DATE(transaction_date)')
                ->orderBy('sale_date')
                ->pluck('total', 'sale_date');

            for ($date = $periodStart->copy(); $date->lte($periodEnd); $date->addDay()) {
                $chartLabels[] = $date->format('d/m');
                $chartData[] = (float) ($dailyTotals[$date->toDateString()] ?? 0);
            }
        }

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
