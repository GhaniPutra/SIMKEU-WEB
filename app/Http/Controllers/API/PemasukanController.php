<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            $query = Sale::with(['user', 'customer', 'saleItems.product'])
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

            $sale->load(['user', 'customer', 'saleItems.product']);

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
                'customer_id' => 'nullable|exists:customers,id',
                'payment_method' => 'required|in:tunai,transfer,qris',
                'discount' => 'nullable|numeric|min:0',
                'tax' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
            ]);

            // Buat transaksi penjualan
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'invoice_number' => 'INV-' . now()->format('YmdHis'),
                'transaction_date' => now(),
                'payment_method' => $validated['payment_method'],
                'subtotal' => collect($validated['items'])->sum(function ($item) {
                    return $item['quantity'] * $item['price'];
                }),
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'total' => 0, // Will be updated below
                'notes' => $validated['notes'] ?? null,
            ]);

            // Buat item penjualan
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['price'];
                $subtotal += $itemTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_at_sale' => $item['price'],
                    'subtotal' => $itemTotal,
                ]);

                // Kurangi stok produk
                $product = Product::find($item['product_id']);
                $product->update(['stock' => $product->stock - $item['quantity']]);
            }

            // Update total penjualan
            $total = $subtotal - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
            $sale->update([
                'subtotal' => $subtotal,
                'total' => $total,
            ]);

            $sale->load(['user', 'customer', 'saleItems.product']);

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

            $sales = Sale::where('user_id', auth()->id())
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->with(['user', 'customer', 'saleItems.product'])
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

            $products = Product::with(['saleItems' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('sale', function($q) {
                    $q->where('user_id', auth()->id());
                })->whereBetween('created_at', [$startDate, $endDate]);
            }])->get()->map(function ($product) {
                $totalQuantity = $product->saleItems->sum('quantity');
                $totalRevenue = $product->saleItems->sum('subtotal');

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
