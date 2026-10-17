<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user || ! $user->store_id) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where($model->qualifyColumn('store_id'), $user->store_id);
    }
}
