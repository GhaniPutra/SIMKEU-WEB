{{-- ============================================================
     resources/views/pengeluaran/create.blade.php
     Halaman input pengeluaran baru untuk SIMKEU
============================================================ --}}
@extends('layouts.app')

@section('title', 'Input Pengeluaran - SIMKEU')
@section('page-title', 'Input Pengeluaran')
@section('breadcrumb', 'Keuangan / Pengeluaran / Baru')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/pengeluaran.css') }}">
@endsection

@section('content')
    <div class="page-header fade-up">
        <div>
            <h2>Tambah Pengeluaran Baru</h2>
            <p class="page-description">Catat biaya operasional seperti beli stok, gaji, listrik, sewa, atau transportasi.</p>
        </div>
        <div>
            <a href="{{ Auth::user()->hasPermission('expenses.view') ? route('pengeluaran.index') : route('home') }}" class="btn-qa btn-qa-outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger fade-up" role="alert">
            <strong>Periksa kembali input Anda:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pengeluaran.store') }}" method="POST" class="expense-form fade-up" id="expenseForm">
        @csrf

        <div class="form-grid">
            <div class="form-card">
                <div class="form-card-header">
                    <h3>Detail Pengeluaran</h3>
                    <p>Isi rincian pengeluaran untuk pencatatan dan laporan keuangan.</p>
                </div>

                {{-- Input tanggal pengeluaran --}}
                <div class="form-group">
                    <label for="expense_date">Tanggal Pengeluaran</label>
                    <input type="date" id="expense_date" name="expense_date" class="form-control"
                           value="{{ old('expense_date', now()->toDateString()) }}" required>
                </div>

                {{-- Pilih kategori pengeluaran --}}
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Pilih kategori --</option>
                        <option value="beli_stok" {{ old('category') === 'beli_stok' ? 'selected' : '' }}>Beli Stok</option>
                        <option value="gaji" {{ old('category') === 'gaji' ? 'selected' : '' }}>Gaji</option>
                        <option value="listrik" {{ old('category') === 'listrik' ? 'selected' : '' }}>Listrik</option>
                        <option value="air" {{ old('category') === 'air' ? 'selected' : '' }}>Air</option>
                        <option value="sewa" {{ old('category') === 'sewa' ? 'selected' : '' }}>Sewa</option>
                        <option value="transport" {{ old('category') === 'transport' ? 'selected' : '' }}>Transport</option>
                        <option value="lainnya" {{ old('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>


                {{-- Nominal pengeluaran --}}
                <div class="form-group">
                    <label for="amount">Jumlah Nominal</label>
                    <input type="number" id="amount" name="amount" class="form-control"
                           value="{{ old('amount') }}" min="0" step="0.01" required>
                </div>

                {{-- Deskripsi pengeluaran --}}
                <div class="form-group">
                    <label for="description">Keterangan</label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              placeholder="Contoh: Pembelian stok bahan baku untuk minggu ini">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="form-card summary-card">
                <div class="form-card-header">
                    <h3>Ringkasan</h3>
                    <p>Pastikan semua data sudah benar sebelum menyimpan.</p>
                </div>

                <div class="summary-row">
                    <span>Kategori dipilih</span>
                    <strong id="summaryCategory">Belum ada</strong>
                </div>
                <div class="summary-row">
                    <span>Nominal</span>
                    <strong id="summaryAmount">Rp 0</strong>
                </div>
                <div class="summary-row description-row">
                    <span>Deskripsi</span>
                    <strong id="summaryDescription">Belum ada keterangan</strong>
                </div>

                <button type="submit" class="btn-qa btn-qa-success btn-submit">
                    <i class="fas fa-save"></i> Simpan Pengeluaran
                </button>
            </div>
        </div>
    </form>
@endsection

