@extends('layouts.app')

@section('title', 'Tambah Kelas | ' . config('app.name'))
@section('page-title', 'Tambah Kelas')
@section('page-subtitle', 'Buat entri kelas baru untuk digunakan siswa.')

@section('content')
<form method="POST" action="{{ route('admin.classrooms.store') }}" class="space-y-6">
    @csrf
    @include('admin.classrooms.partials.form')
</form>
@endsection
