@extends('layouts.app')
@section('title', 'Buat Role - SIMKEU')
@section('page-title', 'Buat Role')
@section('breadcrumb', 'Pengaturan / Role & Izin / Buat')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/roles/roles.css') }}">
@endsection

@section('content')
@include('roles._form', ['action' => route('roles.store'), 'method' => 'POST'])
@endsection

@section('js')
    <script src="{{ asset('js/roles/form.js') }}"></script>
@endsection
