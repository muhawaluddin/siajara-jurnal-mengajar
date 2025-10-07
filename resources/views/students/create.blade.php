@extends('layouts.app')

@section('title', 'Tambah Siswa | ' . config('app.name'))
@section('page-title', 'Tambah Siswa')
@section('page-subtitle', 'Isikan data siswa baru ke dalam sistem.')

@section('content')
<form method="POST" action="{{ route('admin.students.store') }}" class="space-y-6">
    @csrf
    @include('students.partials.form')
</form>
@endsection
