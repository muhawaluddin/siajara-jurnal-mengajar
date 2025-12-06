@extends('layouts.app')

@section('title', 'Ubah Nilai | ' . config('app.name'))
@section('page-title', 'Ubah Nilai Mata Pelajaran')
@section('page-subtitle', 'Perbarui penilaian yang sudah dicatat.')

@section('content')
<form method="POST" action="{{ route('web.grades.update', $assessment) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('grades.partials.form', ['assessment' => $assessment])
</form>
@endsection
