@extends('layouts.app')

{{-- Halaman formulir tambah pegawai baru --}}
@section('title', 'Tambah Pegawai - SIMKEU')
@section('page-title', 'Tambah Pegawai')
@section('breadcrumb', 'Pengaturan / Pegawai / Tambah')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees.css') }}">
@endsection

@section('content')
<div class="form-container">
    <div class="form-card-header">
        <i class="fas fa-user-plus form-icon"></i>
        <h1>Tambah Pegawai Baru</h1>
    </div>

    <div class="content-card">
        <div class="content-card-body">
            <form action="{{ route('employees.store') }}" method="POST">
                @include('employees._form')
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
    <script src="{{ asset('js/employees.js') }}"></script>
@endsection
