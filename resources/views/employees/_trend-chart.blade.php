@if($trendHasTransactions)
    <div class="employee-chart-wrap">
        <canvas id="{{ $chartId }}" role="img" aria-label="Grafik pendapatan harian untuk {{ $analyticsPeriod }} hari terakhir"></canvas>
    </div>
@else
    <div class="employee-empty chart-empty">
        <i class="fas fa-chart-line"></i>
        <strong>Belum ada transaksi pada periode ini</strong>
        <span>Grafik akan terisi berdasarkan penjualan yang tercatat dalam {{ $analyticsPeriod }} hari terakhir.</span>
    </div>
@endif
