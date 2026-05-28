@extends('layouts.app')

{{-- Halaman formulir edit data pegawai --}}
@section('title', 'Edit Pegawai - SIMKEU')
@section('page-title', 'Edit Pegawai')
@section('breadcrumb', 'Pengaturan / Pegawai / Edit')

@section('css')
    {{-- Muat stylesheet khusus halaman edit pegawai --}}
    <link rel="stylesheet" href="{{ asset('css/employees/edit.css') }}">
    {{-- Muat stylesheet khusus komponen form pegawai --}}
    <link rel="stylesheet" href="{{ asset('css/employees/_form.css') }}">
@endsection

@section('content')
<div class="form-container">
    {{-- Header halaman dengan ikon dan judul --}}
    <div class="form-card-header">
        <div class="form-header-title">
            <div class="form-icon-wrapper">
                <i class="fas fa-user-gear"></i>
            </div>
            <div>
                <h1>Edit Pegawai</h1>
                <p>Perbarui informasi akun, profil, dan hak akses pegawai</p>
            </div>
        </div>
    </div>

    {{-- Kontainer utama card form --}}
    <div class="content-card">
        <div class="content-card-body">
            <form action="{{ route('employees.update', $employee) }}" method="POST">
                @method('PUT')
                {{-- Sertakan form partial yang dipakai bersama antara create dan edit --}}
                @include('employees._form')

                {{-- Tombol aksi form: batal dan perbarui --}}
                <div class="form-actions">
                    <a href="{{ route('employees.index') }}" class="btn-cancel">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
    {{-- Muat skrip khusus halaman edit pegawai --}}
    <script src="{{ asset('js/employees/edit.js') }}"></script>
    {{-- Muat skrip khusus komponen form pegawai --}}
    <script src="{{ asset('js/employees/_form.js') }}"></script>
@endsection
