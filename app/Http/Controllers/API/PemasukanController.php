<?php

namespace App\Http\Controllers\API;

use App\Events\StoreDataChanged;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PemasukanController extends Controller
{
    /**
     * Menampilkan daftar penjualan dengan statistik
     * GET /api/sales
     */
    public function index(Request $request)
    {
        try {
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun', now()->year);
            $search = $request->input('search', '');
            $metode = $request->input('metode', '');

            // Rentang tanggal bulan terpilih
            $awalBulan = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $akhirBulan = $awalBulan->copy()->endOfMonth();

            // Query dasar
            $query = Sale::with(['user', 'saleItems.product'])
                ->where('user_id', auth()->id())
                ->whereBetween('transaction_date', [$awalBulan, $akhirBulan]);

            // Filter pencarian
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            // Filter metode pembayaran
            if ($metode !== '') {
                $query->where('payment_method', $metode);
            }

            // Paginate
            $perPage = $request->input('per_page', 15);
            $sales = $query->orderByDesc('transaction_date')->paginate($perPage);

            // Statistik
            $stats = [
                'total_subtotal' => Sale::where('user_id', auth()->id())->whereBetween('transaction_date', [$awalBulan, $akhirBulan])->sum('subtotal'),
                'total_discount' => Sale::where('user_id', auth()->id())->whereBetween('transaction_date', [$awalBulan, $akhirBulan])->sum('discount'),
                'total_tax' => Sale::where('user_id', auth()->id())->whereBetween('transaction_date', [$awalBulan, $akhirBulan])->sum('tax'),
                'total_net' => Sale::where('user_id', auth()->id())->whereBetween('transaction_date', [$awalBulan, $akhirBulan])->sum('total'),
                'transaction_count' => Sale::where('user_id', auth()->id())->whereBetween('transaction_date', [$awalBulan, $akhirBulan])->count(),
            ];

            $stats['average_transaction'] = $stats['transaction_count'] > 0 
                ? $stats['total_net'] / $stats['transaction_count'] 
                : 0;

            return response()->json([
                'success' => true,
                'message' => 'Daftar penjualan berhasil diambil',
                'data' => $sales,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu penjualan
     * GET /api/sales/{id}
     */
    public function show(Sale $sale)
    {
        try {
            if ($sale->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Transaksi ini bukan milik Anda.'
                ], 403);
            }

            $sale->load(['user', 'saleItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Detail penjualan berhasil diambil',
                'data' => $sale
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan penjualan baru
     * POST /api/sales
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'payment_method' => 'required|in:tunai,transfer,qris',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('store_id', $request->user()->store_id)],
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            $itemSubtotals = collect($validated['items'])
                ->map(fn ($item) => round((float) $item['quantity'] * (float) $item['price'], 2));
            $subtotal = round($itemSubtotals->sum(), 2);
            $discount = round((float) ($validated['discount'] ?? 0), 2);
            $tax = round((float) ($validated['tax'] ?? 0), 2);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Diskon tidak boleh melebihi subtotal transaksi.',
                ]);
            }

            // Nilai diskon/pajak yang dikirim merupakan nominal aktual transaksi.
            // Rincian item tetap ditandai tidak tersedia karena endpoint ini
            // hanya menerima nilai diskon dan pajak pada tingkat transaksi.
            $sale = DB::transaction(function () use ($validated, $itemSubtotals, $subtotal, $discount, $tax) {
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'invoice_number' => 'INV-' . now()->format('YmdHis'),
                    'transaction_date' => now(),
                    'payment_method' => $validated['payment_method'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => round($subtotal - $discount + $tax, 2),
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $index => $item) {
                    $itemTotal = $itemSubtotals[$index];

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price_at_sale' => $item['price'],
                        'subtotal' => $itemTotal,
                        'discount' => null,
                        'tax' => null,
                    ]);

                    $product = Product::find($item['product_id']);
                    $product->update(['stock' => $product->stock - $item['quantity']]);
                }

                $sale->update([
                    'subtotal' => $subtotal,
                    'total' => round($subtotal - $discount + $tax, 2),
                ]);

                return $sale;
            });

            $sale->load(['user', 'saleItems.product']);
            StoreDataChanged::dispatch($request->user()->store_id, ['sales', 'inventory']);

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil dicatat',
                'data' => $sale
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan penjualan berdasarkan periode
     * GET /api/sales/report/period
     */
    public function reportByPeriod(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if (!$startDate || !$endDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal mulai dan tanggal akhir harus disediakan'
                ], 400);
            }

            $periodStart = Carbon::parse($startDate)->startOfDay();
            $periodEnd = Carbon::parse($endDate)->endOfDay();

            $sales = Sale::where('user_id', auth()->id())
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->with(['user', 'saleItems.product'])
                ->orderByDesc('transaction_date')
                ->get();

            $stats = [
                'total_subtotal' => $sales->sum('subtotal'),
                'total_discount' => $sales->sum('discount'),
                'total_tax' => $sales->sum('tax'),
                'total_net' => $sales->sum('total'),
                'transaction_count' => $sales->count(),
            ];

            $stats['average_transaction'] = $stats['transaction_count'] > 0
                ? $stats['total_net'] / $stats['transaction_count']
                : 0;

            return response()->json([
                'success' => true,
                'message' => 'Laporan penjualan berhasil diambil',
                'data' => $sales,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan penjualan berdasarkan produk
     * GET /api/sales/report/product
     */
    public function reportByProduct(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now());
            $periodStart = Carbon::parse($startDate)->startOfDay();
            $periodEnd = Carbon::parse($endDate)->endOfDay();

            $products = Product::with(['saleItems' => function ($query) use ($periodStart, $periodEnd) {
                $query->whereHas('sale', function ($q) use ($periodStart, $periodEnd) {
                    $q->where('user_id', auth()->id())
                        ->whereBetween('transaction_date', [$periodStart, $periodEnd]);
                });
            }])->get()->map(function ($product) {
                $totalQuantity = $product->saleItems->sum('quantity');
                $totalRevenue = $product->saleItems->sum(fn ($item) =>
                    (float) $item->subtotal - (float) ($item->discount ?? 0)
                );

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'total_quantity' => $totalQuantity,
                    'total_revenue' => $totalRevenue,
                    'average_price' => $totalQuantity > 0 ? $totalRevenue / $totalQuantity : 0,
                ];
            })->sortByDesc('total_revenue');

            return response()->json([
                'success' => true,
                'message' => 'Laporan produk berhasil diambil',
                'data' => $products->values()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
