@if($trendHasTransactions)
    <div class="employee-chart-wrap">
        <canvas id="{{ $chartId }}" role="img" aria-label="Grafik pendapatan untuk {{ $periodLabel }}"></canvas>
    </div>
@else
    <div class="employee-empty chart-empty">
        <i class="fas fa-chart-line"></i>
        <strong>Belum ada transaksi pada periode ini</strong>
        <span>Grafik akan terisi berdasarkan transaksi pegawai ini pada {{ $periodLabel }}.</span>
    </div>
@endif
