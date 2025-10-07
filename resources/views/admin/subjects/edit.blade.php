@extends('layouts.app')

@section('title', 'Ubah Mata Pelajaran | ' . config('app.name'))
@section('page-title', 'Ubah Mata Pelajaran')
@section('page-subtitle', 'Perbarui nama mata pelajaran jika diperlukan.')

@section('content')
<form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.subjects.partials.form', ['subject' => $subject])
</form>
@endsection
