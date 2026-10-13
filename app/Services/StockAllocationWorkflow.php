<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAllocationWorkflow
{
    public function create(User $owner, User $employee, int $productId, int $quantity): StockAllocation
    {
        return DB::transaction(function () use ($owner, $employee, $productId, $quantity) {
            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            $this->ensureAvailableStock($product, $quantity);

            $product->decrement('stock', $quantity);

            return StockAllocation::create([
                'owner_user_id' => $owner->id,
                'employee_user_id' => $employee->id,
                'product_id' => $product->id,
                'requested_quantity' => $quantity,
                'status' => StockAllocation::STATUS_PENDING,
            ]);
        });
    }

    public function reviewByEmployee(User $employee, int $allocationId, array $data): StockAllocation
    {
        return $this->reviewPending($employee, $allocationId, $data);
    }

    public function followUpByOwner(User $owner, int $allocationId, array $data): array
    {
        return DB::transaction(function () use ($owner, $allocationId, $data) {
            $allocation = StockAllocation::query()->lockForUpdate()->findOrFail($allocationId);
            $this->ensureOwner($owner, $allocation);

            $action = $data['action'];
            $product = Product::query()->lockForUpdate()->findOrFail($allocation->product_id);
            $employee = User::query()->findOrFail($allocation->employee_user_id);

            if ($action === 'cancel') {
                if (! in_array($allocation->status, [
                    StockAllocation::STATUS_PENDING,
                    StockAllocation::STATUS_DECLINED,
                    StockAllocation::STATUS_NEEDS_REVIEW,
                ], true)) {
                    $this->invalid('action', 'Hanya alokasi pending atau needs_review yang dapat dibatalkan.');
                }

                $returned = (int) $data['returned_quantity'];
                $maximumReturn = match ($allocation->status) {
                    StockAllocation::STATUS_PENDING => (int) $allocation->requested_quantity,
                    StockAllocation::STATUS_NEEDS_REVIEW => min(
                        (int) $allocation->actual_received_quantity,
                        (int) $allocation->requested_quantity
                    ),
                    default => 0,
                };

                if ($returned > $maximumReturn) {
                    $this->invalid('returned_quantity', "Jumlah yang dikembalikan tidak boleh melebihi {$maximumReturn}.");
                }

                if ($returned > 0) {
                    $product->increment('stock', $returned);
                }

                $allocation->status = StockAllocation::STATUS_CANCELLED;
                $allocation->confirmed_quantity = 0;
                $allocation->returned_quantity = $returned;
                $allocation->resolution_action = 'cancel';
                $allocation->resolved_by_user_id = $owner->id;
                $allocation->resolved_at = now();
                $allocation->save();

                return ['allocation' => $allocation, 'resent_allocation' => null];
            }

            if (! in_array($allocation->status, [StockAllocation::STATUS_DECLINED, StockAllocation::STATUS_NEEDS_REVIEW], true)) {
                $this->invalid('action', 'Tindak lanjut hanya tersedia untuk alokasi yang ditolak atau memerlukan tinjauan.');
            }

            $actual = (int) $allocation->actual_received_quantity;
            $quantityToAccept = match ($action) {
                'accept_actual' => $actual,
                'resolve_difference' => (int) $data['confirmed_quantity'],
                'resend' => $actual,
            };

            if (in_array($action, ['accept_actual', 'resolve_difference'], true)) {
                if ($quantityToAccept < 1) {
                    $this->invalid('confirmed_quantity', 'Jumlah yang disetujui harus minimal 1. Batalkan alokasi jika tidak ada stok yang diterima.');
                }
                $quantityToReserve = $allocation->status === StockAllocation::STATUS_DECLINED
                    ? $quantityToAccept
                    : max(0, $quantityToAccept - (int) $allocation->requested_quantity);
                if ($quantityToReserve > 0) {
                    $this->ensureAvailableStock($product, $quantityToReserve, 'confirmed_quantity');
                    $product->decrement('stock', $quantityToReserve);
                }

                $allocation->requested_quantity = $quantityToAccept;
                $allocation->confirmed_quantity = null;
                $allocation->status = StockAllocation::STATUS_PENDING;
                $allocation->resolution_action = $action;
                $allocation->resolved_by_user_id = $owner->id;
                $allocation->resolved_at = now();
                $allocation->save();

                return ['allocation' => $allocation, 'resent_allocation' => null];
            }

            if ($action === 'resend') {
                $shortfall = (int) $allocation->requested_quantity - $actual;
                if ($shortfall <= 0) {
                    $this->invalid('action', 'Pengiriman ulang hanya berlaku jika jumlah aktual kurang dari alokasi.');
                }
                $quantityToReserve = $allocation->status === StockAllocation::STATUS_DECLINED
                    ? (int) $allocation->requested_quantity
                    : $shortfall;
                $this->ensureAvailableStock($product, $quantityToReserve, 'action');
                $product->decrement('stock', $quantityToReserve);

                if ($actual > 0) {
                    $allocation->requested_quantity = $actual;
                    $allocation->confirmed_quantity = null;
                    $allocation->status = StockAllocation::STATUS_PENDING;
                    $allocation->resolution_action = 'resend';
                    $allocation->resolved_by_user_id = $owner->id;
                    $allocation->resolved_at = now();
                    $allocation->save();
                } else {
                    $allocation->status = StockAllocation::STATUS_CANCELLED;
                    $allocation->returned_quantity = 0;
                    $allocation->resolution_action = 'resend';
                    $allocation->resolved_by_user_id = $owner->id;
                    $allocation->resolved_at = now();
                    $allocation->save();
                }

                $resentAllocation = StockAllocation::create([
                    'owner_user_id' => $allocation->owner_user_id,
                    'employee_user_id' => $allocation->employee_user_id,
                    'product_id' => $allocation->product_id,
                    'requested_quantity' => $shortfall,
                    'status' => StockAllocation::STATUS_PENDING,
                    'revised_from_allocation_id' => $allocation->id,
                ]);

                return ['allocation' => $allocation, 'resent_allocation' => $resentAllocation];
            }

            $this->invalid('action', 'Tindak lanjut alokasi tidak dikenal.');
        });
    }

    private function reviewPending(User $employee, int $allocationId, array $data): StockAllocation
    {
        return DB::transaction(function () use ($employee, $allocationId, $data) {
            $allocation = StockAllocation::query()->lockForUpdate()->findOrFail($allocationId);
            $this->ensureEmployee($employee, $allocation);

            if ($allocation->status !== StockAllocation::STATUS_PENDING) {
                $this->invalid('decision', 'Alokasi ini tidak lagi berstatus pending.');
            }

            if ($data['decision'] === 'accept') {
                $quantity = (int) $data['confirmed_quantity'];
                if ($quantity !== (int) $allocation->requested_quantity) {
                    $this->invalid('confirmed_quantity', 'Jumlah fisik harus sama dengan jumlah alokasi.');
                }

                $this->creditEmployeeStock($allocation->employee_user_id, $allocation->product_id, $quantity);
                $allocation->confirmed_quantity = $quantity;
                $allocation->actual_received_quantity = $quantity;
                $allocation->difference_quantity = 0;
                $allocation->status = StockAllocation::STATUS_ACCEPTED;
            } else {
                $actual = (int) $data['actual_received_quantity'];
                $product = Product::query()->lockForUpdate()->findOrFail($allocation->product_id);
                $product->increment('stock', $allocation->requested_quantity);

                $allocation->actual_received_quantity = $actual;
                $allocation->difference_quantity = $actual - (int) $allocation->requested_quantity;
                $allocation->decline_reason = trim($data['decline_reason']);
                $allocation->confirmed_quantity = null;
                $allocation->status = StockAllocation::STATUS_DECLINED;
            }

            $allocation->reviewed_by_user_id = $employee->id;
            $allocation->reviewed_at = now();
            $allocation->save();

            return $allocation;
        });
    }

    private function creditEmployeeStock(int $employeeId, int $productId, int $quantity): void
    {
        if ($quantity === 0) {
            return;
        }

        $now = now();
        DB::table('product_user')->insertOrIgnore([
            'user_id' => $employeeId,
            'product_id' => $productId,
            'stock' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('product_user')
            ->where('user_id', $employeeId)
            ->where('product_id', $productId)
            ->increment('stock', $quantity, ['updated_at' => $now]);
    }

    private function ensureAvailableStock(Product $product, int $quantity, string $field = 'requested_quantity'): void
    {
        if ($quantity <= 0 || $product->stock < $quantity) {
            $this->invalid($field, "Stok pemilik tidak mencukupi. Stok tersedia: {$product->stock}");
        }
    }

    private function ensureOwner(User $owner, StockAllocation $allocation): void
    {
        if ($owner->role !== 'admin' || (int) $allocation->owner_user_id !== (int) $owner->id) {
            abort(403, 'Anda tidak berhak mengelola alokasi ini.');
        }
    }

    private function ensureEmployee(User $employee, StockAllocation $allocation): void
    {
        if ($employee->role !== 'pegawai' || (int) $allocation->employee_user_id !== (int) $employee->id) {
            abort(403, 'Anda tidak berhak mengonfirmasi alokasi ini.');
        }
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => [$message]]);
    }
}
