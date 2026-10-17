@extends('layouts.app')

@section('title', 'Detail Pengeluaran - SIMKEU')
@section('page-title', 'Detail Pengeluaran')
@section('breadcrumb', 'Beranda / Pengeluaran / Detail')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/finance-details.css') }}">
@endsection

@section('content')
@php
    $katInfo = $kategoriLabel[$expense->category] ?? [
        'label' => ucfirst($expense->category),
        'icon' => 'fa-ellipsis',
    ];
@endphp

<div class="finance-detail finance-detail--expense">
    <div class="fd-toolbar">
        <a href="{{ route('pengeluaran.index', request()->query()) }}" class="fd-back-link">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            <span>Kembali ke Pengeluaran</span>
        </a>
    </div>

    <section class="fd-hero fd-hero--expense" aria-labelledby="expense-title">
        <div class="fd-hero-main">
            <div class="fd-eyebrow">
                <span class="fd-eyebrow-icon"><i class="fas {{ $katInfo['icon'] }}" aria-hidden="true"></i></span>
                Catatan Pengeluaran
            </div>
            <h1 class="fd-hero-title" id="expense-title">{{ $katInfo['label'] }}</h1>
            <div class="fd-hero-subtitle">Pengeluaran #{{ $expense->id }}</div>
            <div class="fd-inline-meta">
                <span class="fd-inline-meta-item">
                    <i class="fas fa-calendar-day" aria-hidden="true"></i>
                    {{ $expense->expense_date->translatedFormat('l, d F Y') }}
                </span>
                <span class="fd-meta-dot" aria-hidden="true"></span>
                <span class="fd-inline-meta-item">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    Dicatat oleh {{ $expense->user?->name ?? '—' }}
                </span>
            </div>
        </div>

        <div class="fd-hero-total">
            <span class="fd-total-label">Total pengeluaran</span>
            <strong class="fd-total-value">Rp {{ number_format($expense->amount, 0, ',', '.') }}</strong>
            <span class="fd-total-caption">Nilai yang dibebankan</span>
        </div>
    </section>

    <section class="fd-card" aria-labelledby="expense-record-title">
        <div class="fd-card-header">
            <h2 class="fd-section-heading" id="expense-record-title">
                <span class="fd-section-icon fd-expense-icon"><i class="fas fa-circle-info" aria-hidden="true"></i></span>
                Informasi pencatatan
            </h2>
            <span class="fd-card-caption">ID #{{ $expense->id }}</span>
        </div>
        <div class="fd-overview-grid fd-overview-grid--two">
            <div class="fd-overview-item">
                <div class="fd-overview-label">Waktu pencatatan</div>
                <div class="fd-overview-value">{{ $expense->created_at?->translatedFormat('d M Y, H:i') ?? '—' }} WIB</div>
            </div>
            <div class="fd-overview-item">
                <div class="fd-overview-label">Terakhir diperbarui</div>
                <div class="fd-overview-value">{{ $expense->updated_at?->translatedFormat('d M Y, H:i') ?? '—' }} WIB</div>
            </div>
        </div>
    </section>

    <section class="fd-note" aria-labelledby="expense-note-title">
        <span class="fd-note-icon fd-expense-icon"><i class="fas fa-align-left" aria-hidden="true"></i></span>
        <div>
            <span class="fd-note-label" id="expense-note-title">Keterangan</span>
            <p class="fd-note-text">{{ $expense->description ?: 'Tidak ada keterangan tambahan untuk pengeluaran ini.' }}</p>
        </div>
    </section>
</div>
@endsection
