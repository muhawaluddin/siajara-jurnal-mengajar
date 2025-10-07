@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran | ' . config('app.name'))
@section('page-title', 'Tambah Mata Pelajaran')
@section('page-subtitle', 'Tambahkan mata pelajaran baru untuk dicatat dalam jurnal.')

@section('content')
<form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-6">
    @csrf
    @include('admin.subjects.partials.form')
</form>
@endsection
