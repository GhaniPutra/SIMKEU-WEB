@extends('layouts.app')

{{-- Halaman formulir tambah pegawai baru --}}
@section('title', 'Tambah Pegawai - SIMKEU')
@section('page-title', 'Tambah Pegawai')
@section('breadcrumb', 'Pengaturan / Pegawai / Tambah')

@section('css')
    {{-- Muat stylesheet khusus halaman tambah pegawai --}}
    <link rel="stylesheet" href="{{ asset('css/employees/create.css') }}">
    {{-- Muat stylesheet khusus komponen form pegawai --}}
    <link rel="stylesheet" href="{{ asset('css/employees/_form.css') }}">
@endsection

@section('content')
<div class="form-container">
    {{-- Header halaman dengan ikon dan judul --}}
    <div class="form-card-header">
        <div class="form-header-title">
            <div class="form-icon-wrapper">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <h1>Tambah Pegawai</h1>
                <p>Daftarkan akun pegawai baru ke dalam sistem keuangan SIMKEU</p>
            </div>
        </div>
    </div>

    {{-- Kontainer utama card form --}}
    <div class="content-card">
        <div class="content-card-body">
            <form action="{{ route('employees.store') }}" method="POST">
                {{-- Sertakan form partial yang dipakai bersama antara create dan edit --}}
                @include('employees._form')

                {{-- Tombol aksi form: batal dan simpan --}}
                <div class="form-actions">
                    <a href="{{ route('employees.index') }}" class="btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
    {{-- Muat skrip khusus halaman tambah pegawai --}}
    <script src="{{ asset('js/employees/create.js') }}"></script>
    {{-- Muat skrip khusus komponen form pegawai --}}
    <script src="{{ asset('js/employees/_form.js') }}"></script>
@endsection
