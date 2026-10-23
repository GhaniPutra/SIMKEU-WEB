@extends('layouts.app')
@section('title', 'Ubah Role - SIMKEU')
@section('page-title', 'Ubah Role')
@section('breadcrumb', 'Pengaturan / Role & Izin / Ubah')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/roles/roles.css') }}?v=3">
@endsection

@section('content')
@include('roles._form', ['action' => route('roles.update', $role), 'method' => 'PUT'])
@endsection

@section('js')
    <script src="{{ asset('js/roles/form.js') }}"></script>
@endsection
