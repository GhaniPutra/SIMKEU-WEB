<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use App\Support\PeriodFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeSalesController extends Controller
{
    public function dashboard(Request $request, User $employee)
    {
        // Pastikan hanya admin yang bisa akses
        if (! Auth::user()->hasPermission('employees.sales.view')) {
            abort(403, 'Unauthorized');
        }

        // Ambil data penjualan untuk pegawai ini
        $now = now();
        [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ] = PeriodFilter::resolveFromRequest($request, $now);
        $employeeSales = Sale::where('user_id', $employee->id);
        $periodSales = PeriodFilter::apply(clone $employeeSales, 'transaction_date', $periodStart, $periodEnd);

        $sales = (clone $periodSales)
            ->with('saleItems.product')
            ->orderByDesc('transaction_date')
            ->paginate(15)
            ->withQueryString();

        $totalSales = (clone $periodSales)->count();
        $totalRevenue = (clone $periodSales)->sum('total');
        $itemSales = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.store_id', $request->user()->store_id)
            ->where('sales.user_id', $employee->id);
        PeriodFilter::apply($itemSales, 'sales.transaction_date', $periodStart, $periodEnd);
        $totalItemsSold = $itemSales->sum('sale_items.quantity');

        return view('employee_sales.dashboard', compact(
            'employee',
            'sales',
            'period',
            'periodLabel',
            'periodStart',
            'periodEnd',
            'totalSales',
            'totalRevenue',
            'totalItemsSold'
        ));
    }
}
