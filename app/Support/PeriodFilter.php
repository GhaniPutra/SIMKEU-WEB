<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'week' => [$now->copy()->startOfWeek(Carbon::MONDAY), $now->copy()->endOfDay()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
        };

        return [
            'period' => $period,
            'periodLabel' => self::LABELS[$period],
            'periodStart' => $start,
            'periodEnd' => $end,
        ];
    }

    public static function resolveFromRequest(Request $request, ?DateTimeInterface $now = null): array
    {
        $requested = $request->query('period') ?? $request->cookie('dashboardPeriod', 'month');

        if ($requested !== 'custom') {
            return self::resolve($requested, $now);
        }

        // Validasi dilakukan di server agar query string yang dimanipulasi tidak memfilter rentang keliru.
        $dateValidator = Validator::make([
            'start_date' => $request->query('start_date', $request->cookie('dashboardStartDate')),
            'end_date' => $request->query('end_date', $request->cookie('dashboardEndDate')),
        ], [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ], [
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date_format' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.date_format' => 'Format tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
        ]);

        // Cookie yang rusak tidak boleh membuat pengguna terjebak pada redirect validasi.
        if (! $request->query->has('period') && $dateValidator->fails()) {
            return self::resolve('month', $now);
        }

        $dates = $dateValidator->validate();

        $timezone = $now instanceof Carbon
            ? $now->getTimezone()
            : ($now ? Carbon::instance($now)->getTimezone() : config('app.timezone'));
        $start = Carbon::createFromFormat('!Y-m-d', $dates['start_date'], $timezone);
        $end = Carbon::createFromFormat('!Y-m-d', $dates['end_date'], $timezone)->endOfDay();

        // Rentang custom memakai tanggal lokal aplikasi dan mencakup seluruh hari akhir.
        return [
            'period' => 'custom',
            'periodLabel' => $start->translatedFormat('j M Y') . ' – ' . $end->translatedFormat('j M Y'),
            'periodStart' => $start,
            'periodEnd' => $end,
        ];
    }

    public static function apply($query, string $column, Carbon|string|null $start, Carbon|string|null $end)
    {
        if ($start && $end) {
            // Batas atas eksklusif menjaga seluruh tanggal akhir tetap masuk pada driver SQLite maupun MySQL.
            if (is_string($end) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $end) === 1) {
                $endExclusive = Carbon::createFromFormat('!Y-m-d', $end, config('app.timezone'))->addDay()->toDateString();

                return $query->where($column, '>=', $start)->where($column, '<', $endExclusive);
            }

            $query->whereBetween($column, [$start, $end]);
        }

        return $query;
    }
}
