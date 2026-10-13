<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAllocation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_NEEDS_REVIEW = 'needs_review';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'owner_user_id',
        'employee_user_id',
        'product_id',
        'requested_quantity',
        'confirmed_quantity',
        'actual_received_quantity',
        'difference_quantity',
        'returned_quantity',
        'status',
        'decline_reason',
        'reviewed_by_user_id',
        'resolution_action',
        'resolved_by_user_id',
        'revised_from_allocation_id',
        'reviewed_at',
        'resolved_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'requested_quantity' => 'integer',
        'confirmed_quantity' => 'integer',
        'actual_received_quantity' => 'integer',
        'difference_quantity' => 'integer',
        'returned_quantity' => 'integer',
    ];

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function isNeedsReview(): bool
    {
        return $this->status === self::STATUS_NEEDS_REVIEW;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function revisedFrom()
    {
        return $this->belongsTo(self::class, 'revised_from_allocation_id');
    }
}
