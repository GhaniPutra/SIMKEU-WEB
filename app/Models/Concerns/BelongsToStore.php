<?php

namespace App\Models\Concerns;

use App\Models\Scopes\StoreScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToStore
{
    protected static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope());

        static::creating(function (Model $model): void {
            if (! $model->store_id && auth()->user()?->store_id) {
                $model->store_id = auth()->user()->store_id;
            }
        });
    }
}
