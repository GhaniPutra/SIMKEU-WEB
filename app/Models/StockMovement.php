<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const TYPE_MANUAL_WITHDRAWAL = 'manual_withdrawal';
    public const TYPE_AUTO_RETURN = 'auto_return';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'store_id',
        'employee_user_id',
        'product_id',
        'movement_type',
        'actor_user_id',
        'quantity',
        'occurred_at',
        'closing_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'occurred_at' => 'datetime',
        'closing_date' => 'date',
    ];
}
