{{-- ============================================================
     resources/views/pengeluaran/show.blade.php
     Halaman detail satu entri pengeluaran operasional
     Menampilkan informasi lengkap: tanggal, kategori, deskripsi, produk, kasir
============================================================ --}}
@extends('layouts.app')

{{-- Judul tab browser --}}
@section('title', 'Detail Pengeluaran - SIMKEU')

{{-- Judul di topbar --}}
@section('page-title', 'Detail Pengeluaran')

{{-- Breadcrumb navigasi --}}
@section('breadcrumb', 'Beranda / Pengeluaran / Detail')

@section('css')
<style>
    /* ============================================================
       KARTU DETAIL — Wrapper utama konten halaman ini
    ============================================================ */
    .detail-card {
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 20px;
    }

    /* Header kartu: judul + aksi */
    .detail-card-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .detail-card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-card-title i { color: var(--icon-danger); } /* ikon merah */

    .detail-card-body { padding: 24px; }

    /* ============================================================
       HIGHLIGHT BOX — Kotak nominal utama
       Ditampilkan di atas sebagai focal point halaman
    ============================================================ */
    .amount-highlight {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        border-radius: 14px;
        padding: 28px 24px;
        color: #f0f7fe;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    /* Lingkaran dekorasi di pojok kanan atas */
    .amount-highlight::after {
        content: '';
        position: absolute;
        right: -40px; top: -40px;
        width: 160px; height: 160px;
        border-radius: 50%;
        background: rgba(240,247,254,0.07);
    }

    .amount-highlight .ah-label {
        font-size: 0.78rem;
        opacity: 0.78;
        font-weight: 500;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Nominal besar di tengah */
    .amount-highlight .ah-value {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 10px;
    }

    /* Meta info di bawah nominal */
    .amount-highlight .ah-meta {
        font-size: 0.82rem;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .amount-highlight .ah-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ============================================================
       INFO GRID — Dua kolom informasi detail
    ============================================================ */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 640px) {
        .info-grid { grid-template-columns: 1fr; }
    }

    .info-label {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: var(--text-muted);
        margin-bottom: 5px;
    }
    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* ============================================================
       BADGE KATEGORI — Label warna kategori pengeluaran
    ============================================================ */
    .badge-kategori {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #f0f7fe;
    }
    .badge-kategori-beli_stok,
    .badge-kategori-transport { background: var(--blue-700); }
    .badge-kategori-gaji,
    .badge-kategori-air { background: var(--green-600); }
    .badge-kategori-listrik { background: var(--yellow-400); color: var(--yellow-950); }
    .badge-kategori-sewa { background: var(--red-600); }
    .badge-kategori-lainnya { background: var(--blue-800); }

    /* ============================================================
       KOTAK PRODUK — Info produk terkait (untuk kategori beli_stok)
    ============================================================ */
    .produk-box {
        background: #f0f7fe;
        border-radius: 10px;
        padding: 16px 18px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: 20px;
    }

    /* Kotak ikon produk */
    .produk-icon {
        width: 42px; height: 42px;
        background: rgba(var(--primary-rgb),.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--icon-primary);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .produk-info { flex: 1; }
    .produk-name { font-weight: 700; font-size: 0.9rem; color: var(--text-primary); }
    .produk-meta { font-size: 0.78rem; color: var(--text-muted); margin-top: 3px; }

    /* ============================================================
       TOMBOL KEMBALI
    ============================================================ */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 9px;
        font-size: 0.82rem;
        font-weight: 600;
        background: #f0f7fe;
        color: var(--text-primary);
        border: 1px solid var(--border);
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: var(--danger); /* Hover merah untuk halaman pengeluaran */
        color: #f0f7fe;
        border-color: var(--danger);
    }

</style>
@endsection

@section('content')

{{-- Tombol kembali ke daftar pengeluaran --}}
<div style="margin-bottom:20px;" class="fade-up">
    <a href="{{ route('pengeluaran.index', request()->query()) }}" class="btn-back">
        <i class="fas fa-arrow-left fa-xs"></i> Kembali ke Pengeluaran
    </a>
</div>

{{-- ============================================================
     HIGHLIGHT NOMINAL — Fokus utama: nilai pengeluaran
============================================================ --}}
@php
    // Ambil info kategori yang sesuai dari mapping (label, icon, warna)
    $katInfo = $kategoriLabel[$expense->category] ?? [
        'label' => ucfirst($expense->category),
        'icon'  => 'fa-ellipsis',
        'warna' => '#4192f0',
    ];
@endphp

<div class="amount-highlight fade-up">
    <div class="ah-label">Total Pengeluaran</div>
    {{-- Nominal besar --}}
    <div class="ah-value">Rp {{ number_format($expense->amount, 0, ',', '.') }}</div>
    {{-- Meta informasi: tanggal dan kategori --}}
    <div class="ah-meta">
        <span>
            <i class="fas fa-calendar-day fa-xs"></i>
            {{ $expense->expense_date->translatedFormat('d F Y') }}
        </span>
        <span>
            <i class="fas {{ $katInfo['icon'] }} fa-xs"></i>
            {{ $katInfo['label'] }}
        </span>
        <span>
            <i class="fas fa-user fa-xs"></i>
            {{ $expense->user?->name ?? '—' }}
        </span>
    </div>
</div>

{{-- ============================================================
     KARTU DETAIL INFORMASI PENGELUARAN
============================================================ --}}
<div class="detail-card fade-up">
    <div class="detail-card-header">
        <div class="detail-card-title">
            <i class="fas fa-circle-info"></i> Informasi Pengeluaran
        </div>
        {{-- Badge kategori di pojok kanan header --}}
        <span class="badge-kategori badge-kategori-{{ $expense->category }}">
            <i class="fas {{ $katInfo['icon'] }} fa-xs"></i>
            {{ $katInfo['label'] }}
        </span>
    </div>

    <div class="detail-card-body">

        {{-- Grid dua kolom info utama --}}
        <div class="info-grid">

            {{-- Tanggal pengeluaran --}}
            <div class="info-item">
                <div class="info-label">Tanggal</div>
                <div class="info-value">
                    {{ $expense->expense_date->translatedFormat('l, d F Y') }}
                </div>
            </div>

            {{-- Nominal yang dikeluarkan --}}
            <div class="info-item">
                <div class="info-label">Jumlah</div>
                <div class="info-value" style="color:var(--danger);font-size:1.1rem;">
                    Rp {{ number_format($expense->amount, 0, ',', '.') }}
                </div>
            </div>

            {{-- Kategori pengeluaran --}}
            <div class="info-item">
                <div class="info-label">Kategori</div>
                <div class="info-value">{{ $katInfo['label'] }}</div>
            </div>

            {{-- User yang mencatat pengeluaran --}}
            <div class="info-item">
                <div class="info-label">Dicatat Oleh</div>
                <div class="info-value">{{ $expense->user?->name ?? '—' }}</div>
            </div>

            {{-- Waktu entri dibuat di sistem --}}
            <div class="info-item">
                <div class="info-label">Dicatat Pada</div>
                <div class="info-value">
                    {{ $expense->created_at?->translatedFormat('d F Y, H:i') ?? '—' }} WIB
                </div>
            </div>

            {{-- Terakhir diperbarui --}}
            <div class="info-item">
                <div class="info-label">Terakhir Diperbarui</div>
                <div class="info-value">
                    {{ $expense->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }} WIB
                </div>
            </div>

            {{-- Deskripsi / keterangan (span full width) --}}
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="info-label">Deskripsi / Keterangan</div>
                <div class="info-value"
                     style="font-weight:400;color:var(--text-muted);line-height:1.6;">
                    {{-- Tampilkan deskripsi atau tanda strip jika kosong --}}
                    {{ $expense->description ?: '— Tidak ada keterangan tambahan.' }}
                </div>
            </div>

        </div>{{-- /info-grid --}}

    </div>{{-- /detail-card-body --}}
</div>{{-- /detail-card --}}

@endsection
