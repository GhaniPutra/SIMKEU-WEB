<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('store.{storeId}.{resource}', function ($user, $storeId, string $resource) {
    $permissions = match ($resource) {
        'sales' => ['sales.view', 'sales.mobile.view', 'dashboard.view', 'dashboard.mobile.view'],
        'expenses' => ['expenses.view', 'expenses.mobile.view', 'dashboard.view', 'dashboard.mobile.view'],
        'inventory' => [
            'products.view',
            'products.mobile.view',
            'products.stock.add',
            'products.stock.update',
            'products.stock.adjust',
            'pos.mobile.use',
            'stock_allocations.view',
            'stock_allocations.create',
            'stock_allocations.confirm',
            'stock_allocations.review',
            'stock_allocations.transfer',
        ],
        'stock-allocations' => [
            'stock_allocations.view',
            'stock_allocations.create',
            'stock_allocations.confirm',
            'stock_allocations.review',
            'stock_allocations.transfer',
        ],
        'employees' => [
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
        ],
        default => [],
    };

    return (int) $user->store_id === (int) $storeId
        && collect($permissions)->contains(fn (string $permission) => $user->hasPermission($permission));
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
