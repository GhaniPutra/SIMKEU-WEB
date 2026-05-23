@extends('layouts.app')

{{-- Halaman formulir edit data pegawai --}}
@section('title', 'Edit Pegawai - SIMKEU')
@section('page-title', 'Edit Pegawai')
@section('breadcrumb', 'Pengaturan / Pegawai / Edit')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/employees.css') }}">
@endsection

@section('content')
<div class="form-container">
    <div class="form-card-header">
        <i class="fas fa-user-edit form-icon"></i>
        <h1>Edit Pegawai</h1>
    </div>

    <div class="content-card">
        <div class="content-card-body">
            <form action="{{ route('employees.update', $employee) }}" method="POST">
                @method('PUT')
                @include('employees._form')
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
    <script src="{{ asset('js/employees.js') }}"></script>
@endsection
