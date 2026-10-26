<?php

namespace App\Http\Controllers\API;

use App\Events\StoreDataChanged;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PengeluaranController extends Controller
{
    private const KATEGORI_LABEL = [
        'beli_stok' => 'Beli Stok',
        'gaji' => 'Gaji',
        'listrik' => 'Listrik',
        'air' => 'Air',
        'sewa' => 'Sewa',
        'transport' => 'Transport',
        'lainnya' => 'Lainnya',
    ];

    /**
     * Menampilkan daftar pengeluaran dengan statistik
     * GET /api/expenses
     */
    public function index(Request $request)
    {
        try {
            $bulan = $request->input('bulan', now()->month);
            $tahun = $request->input('tahun', now()->year);
            $search = $request->input('search', '');
            $kategori = $request->input('kategori', '');

            // Rentang tanggal bulan terpilih
            $awalBulan = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $akhirBulan = $awalBulan->copy()->endOfMonth();

            // Query dasar
            $query = Expense::with('user')
                ->where('user_id', auth()->id())
                ->whereBetween('expense_date', [
                    $awalBulan->toDateString(),
                    $akhirBulan->toDateString(),
                ]);

            // Filter pencarian
            if ($search !== '') {
                $query->where('description', 'like', "%{$search}%");
            }

            // Filter kategori
            if ($kategori !== '') {
                $query->where('category', $kategori);
            }

            // Paginate
            $perPage = $request->input('per_page', 15);
            $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate($perPage);

            // Statistik
            $stats = [
                'total_month' => Expense::where('user_id', auth()->id())->whereBetween('expense_date', [
                    $awalBulan->toDateString(),
                    $akhirBulan->toDateString(),
                ])->sum('amount'),
                'entry_count' => Expense::where('user_id', auth()->id())->whereBetween('expense_date', [
                    $awalBulan->toDateString(),
                    $akhirBulan->toDateString(),
                ])->count(),
                'largest_amount' => Expense::where('user_id', auth()->id())->whereBetween('expense_date', [
                    $awalBulan->toDateString(),
                    $akhirBulan->toDateString(),
                ])->max('amount') ?? 0,
            ];

            $stats['average'] = $stats['entry_count'] > 0
                ? $stats['total_month'] / $stats['entry_count']
                : 0;

            return response()->json([
                'success' => true,
                'message' => 'Daftar pengeluaran berhasil diambil',
                'data' => $expenses,
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
     * Menampilkan detail satu pengeluaran
     * GET /api/expenses/{id}
     */
    public function show(Expense $expense)
    {
        try {
            if ($expense->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Pengeluaran ini bukan milik Anda.'
                ], 403);
            }

            $expense->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Detail pengeluaran berhasil diambil',
                'data' => $expense
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan pengeluaran baru
     * POST /api/expenses
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'expense_date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'category' => 'required|in:beli_stok,gaji,listrik,air,sewa,transport,lainnya',
                'description' => 'nullable|string|max:500',
            ]);

            // Buat pengeluaran baru
            $expense = Expense::create([
                'user_id' => auth()->id(),
                'expense_date' => $validated['expense_date'],
                'amount' => $validated['amount'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
            ]);

            $expense->load('user');
            StoreDataChanged::dispatch($request->user()->store_id, ['expenses']);

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil dicatat',
                'data' => $expense
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
     * Memperbarui pengeluaran
     * PUT /api/expenses/{id}
     */
    public function update(Request $request, Expense $expense)
    {
        try {
            if ($expense->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Pengeluaran ini bukan milik Anda.'
                ], 403);
            }

            // Validasi input
            $validated = $request->validate([
                'expense_date' => 'sometimes|date',
                'amount' => 'sometimes|numeric|min:0',
                'category' => 'sometimes|in:beli_stok,gaji,listrik,air,sewa,transport,lainnya',
                'description' => 'nullable|string|max:500',
            ]);

            // Update pengeluaran
            $expense->update($validated);
            $expense->load('user');
            StoreDataChanged::dispatch($request->user()->store_id, ['expenses']);

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil diperbarui',
                'data' => $expense
            ], 200);

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
     * Menghapus pengeluaran
     * DELETE /api/expenses/{id}
     */
    public function destroy(Expense $expense)
    {
        try {
            if ($expense->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Pengeluaran ini bukan milik Anda.'
                ], 403);
            }

            $category = $expense->category;
            $amount = $expense->amount;
            $storeId = $expense->store_id;
            $expense->delete();
            StoreDataChanged::dispatch($storeId, ['expenses']);

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran berhasil dihapus',
                'data' => [
                    'category' => $category,
                    'amount' => $amount
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Laporan pengeluaran berdasarkan periode
     * GET /api/expenses/report/period
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

            $expenses = Expense::where('user_id', auth()->id())
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->with('user')
                ->orderByDesc('expense_date')
                ->get();

            $stats = [
                'total' => $expenses->sum('amount'),
                'count' => $expenses->count(),
                'average' => $expenses->count() > 0 ? $expenses->sum('amount') / $expenses->count() : 0,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Laporan pengeluaran berhasil diambil',
                'data' => $expenses,
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
     * Laporan pengeluaran berdasarkan kategori
     * GET /api/expenses/report/category
     */
    public function reportByCategory(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now());

            $expenses = Expense::where('user_id', auth()->id())
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('category')
                ->orderByDesc('total')
                ->get();

            $data = $expenses->map(function ($expense) {
                return [
                    'category' => $expense->category,
                    'category_label' => self::KATEGORI_LABEL[$expense->category] ?? $expense->category,
                    'total' => $expense->total,
                    'count' => $expense->count,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Laporan kategori berhasil diambil',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get kategori list
     * GET /api/expenses/categories
     */
    public static function getCategories()
    {
        return self::KATEGORI_LABEL;
    }
}
