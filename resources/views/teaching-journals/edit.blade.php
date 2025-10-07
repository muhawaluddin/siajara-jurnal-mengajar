@extends('layouts.app')

@section('title', 'Ubah Jurnal | ' . config('app.name'))
@section('page-title', 'Ubah Jurnal Mengajar')
@section('page-subtitle', 'Perbarui catatan mengajar jika terdapat perubahan.')

@section('content')
<form method="POST" action="{{ route('web.teaching-journals.update', $teachingJournal) }}" class="space-y-6">
    @csrf
    @method('PUT')
    @include('teaching-journals.partials.form', ['teachingJournal' => $teachingJournal])
</form>
@endsection
