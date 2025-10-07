@extends('layouts.app')

@section('title', 'Ubah Kelas | ' . config('app.name'))
@section('page-title', 'Ubah Data Kelas')
@section('page-subtitle', 'Perbarui informasi kelas sesuai kebutuhan.')

@section('content')
<form method="POST" action="{{ route('admin.classrooms.update', $classroom) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.classrooms.partials.form', ['classroom' => $classroom])
</form>
@endsection
