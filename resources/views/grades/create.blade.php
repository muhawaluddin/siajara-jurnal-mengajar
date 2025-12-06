@extends('layouts.app')

@section('title', 'Input Nilai | ' . config('app.name'))
@section('page-title', 'Input Nilai Mata Pelajaran')
@section('page-subtitle', 'Catat nilai per pertemuan, UTS, dan UAS sesuai kelas yang diampu.')

@section('content')
<form method="POST" action="{{ route('web.grades.store') }}" class="space-y-6">
    @csrf
    @include('grades.partials.form')
</form>
@endsection
