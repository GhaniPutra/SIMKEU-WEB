@extends('layouts.app')

@section('title', 'Edit Pegawai - SIMKEU')
@section('page-title', 'Edit Pegawai')
@section('breadcrumb', 'Pegawai / Edit')
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/employees/form.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/settings/profile.css') }}?v=14">
@endsection

@section('content')
<main class="employee-form-page employee-edit-page">
    <header class="employee-page-heading">
        <a class="employee-back-link" href="{{ route('employees.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Daftar pegawai</a>
        <span class="employee-page-eyebrow">PENGELOLAAN AKUN</span>
        <h1>Edit pegawai</h1>
        <p>Perbarui profil, foto, role akses, atau kata sandi pegawai.</p>
    </header>

    <form class="employee-account-form" action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('employees._form')
        <footer class="employee-form-actions">
            <a class="employee-button employee-button-secondary" href="{{ route('employees.index') }}">Batal</a>
            <button class="employee-button employee-button-primary" type="submit"><i class="fas fa-check" aria-hidden="true"></i> Simpan perubahan</button>
        </footer>
    </form>
@include('settings._image_cropper')
</main>
@endsection

@section('js')
    <script src="{{ asset('js/settings/image-cropper.js') }}?v=7"></script>
    <script src="{{ asset('js/employees/form.js') }}?v=2"></script>
@endsection
