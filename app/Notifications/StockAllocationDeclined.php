<?php

namespace App\Notifications;

use App\Models\StockAllocation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockAllocationDeclined extends Notification
{
    use Queueable;

    public function __construct(private readonly StockAllocation $allocation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $allocation = $this->allocation->loadMissing(['employee', 'product']);

        return [
            'type' => 'warning',
            'title' => 'Alokasi stok ditolak',
            'message' => $allocation->employee->name.' menolak alokasi '.$allocation->requested_quantity.' unit produk '.$allocation->product->name.'.',
            'reason' => $allocation->decline_reason,
            'allocation_id' => $allocation->id,
            'employee_name' => $allocation->employee->name,
            'product_name' => $allocation->product->name,
            'requested_quantity' => $allocation->requested_quantity,
            'url' => route('employees.manage', [
                'employee' => $allocation->employee,
                'period' => 'all',
                'tab' => 'stock',
            ], false),
        ];
    }
}
