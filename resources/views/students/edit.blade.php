@extends('layouts.app')

@section('title', 'Ubah Siswa | ' . config('app.name'))
@section('page-title', 'Ubah Data Siswa')
@section('page-subtitle', 'Perbarui informasi siswa berikut sesuai kebutuhan.')

@section('content')
<form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('students.partials.form', ['student' => $student])
</form>
@endsection
