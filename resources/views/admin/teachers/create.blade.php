@extends('layouts.app')

@section('title', 'Tambah Guru | ' . config('app.name'))
@section('page-title', 'Tambah Guru')
@section('page-subtitle', 'Buat akun guru baru untuk mengakses aplikasi.')

@section('content')
<form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-6">
    @csrf
    @include('admin.teachers.partials.form')
</form>
@endsection
