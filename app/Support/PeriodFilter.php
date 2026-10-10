<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

final class PeriodFilter
{
    private const LABELS = [
        'all' => 'Semua Periode',
        'today' => 'Hari Ini',
        'week' => 'Minggu Ini',
        'month' => 'Bulan Ini',
    ];

    public static function resolve(mixed $requested, ?DateTimeInterface $now = null): array
    {
        $period = is_string($requested) && array_key_exists($requested, self::LABELS)
            ? $requested
            : 'month';
        $now = $now ? Carbon::instance($now) : Carbon::now();

        [$start, $end] = match ($period) {
            'all' => [null, null],
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        return [
            'period' => $period,
            'periodLabel' => self::LABELS[$period],
            'periodStart' => $start,
            'periodEnd' => $end,
        ];
    }

    public static function apply($query, string $column, Carbon|string|null $start, Carbon|string|null $end)
    {
        if ($start && $end) {
            $query->whereBetween($column, [$start, $end]);
        }

        return $query;
    }
}
