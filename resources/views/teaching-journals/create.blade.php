@extends('layouts.app')

@section('title', 'Tambah Jurnal | ' . config('app.name'))
@section('page-title', 'Tambah Jurnal Mengajar')
@section('page-subtitle', 'Catat sesi mengajar terbaru Anda.')

@section('content')
<form method="POST" action="{{ route('web.teaching-journals.store') }}" class="space-y-6">
    @csrf
    @include('teaching-journals.partials.form')
</form>
@endsection
