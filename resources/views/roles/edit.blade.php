@extends('layouts.app')
@section('title', 'Ubah Role - SIMKEU')
@section('page-title', 'Ubah Role')
@section('breadcrumb', 'Pengaturan / Role & Izin / Ubah')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/roles/roles.css') }}">
@endsection

@section('content')
@include('roles._form', ['action' => route('roles.update', $role), 'method' => 'PUT'])
@endsection

@section('js')
    <script src="{{ asset('js/roles/form.js') }}"></script>
@endsection
