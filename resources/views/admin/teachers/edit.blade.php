@extends('layouts.app')

@section('title', 'Ubah Guru | ' . config('app.name'))
@section('page-title', 'Ubah Data Guru')
@section('page-subtitle', 'Perbarui informasi akun guru.')

@section('content')
<form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.teachers.partials.form', ['teacher' => $teacher])
</form>
@endsection
