<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSalesController extends Controller
{
    public function dashboard(User $employee)
    {
        // Pastikan hanya admin yang bisa akses
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Ambil data penjualan untuk pegawai ini
        $sales = Sale::where('user_id', $employee->id)
            ->with(['saleItems.product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Hitung statistik
        $totalSales = Sale::where('user_id', $employee->id)->count();
        $totalRevenue = Sale::where('user_id', $employee->id)->sum('total');
        $totalItemsSold = Sale::where('user_id', $employee->id)
            ->with('saleItems')
            ->get()
            ->sum(function ($sale) {
                return $sale->saleItems->sum('quantity');
            });

        // Penjualan bulan ini
        $currentMonthSales = Sale::where('user_id', $employee->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        return view('employee_sales.dashboard', compact(
            'employee',
            'sales',
            'totalSales',
            'totalRevenue',
            'totalItemsSold',
            'currentMonthSales'
        ));
    }
}