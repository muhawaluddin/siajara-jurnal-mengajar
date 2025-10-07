@extends('layouts.app')

@section('title', 'Riwayat Absensi | ' . config('app.name'))
@section('page-title', 'Riwayat Absensi')
@section('page-subtitle', 'Telusuri dan kelola catatan absensi yang sudah tersimpan.')

@section('content')
@php
    $statusStyles = [
        'hadir' => 'bg-green-50 text-green-700 border border-green-200',
        'alpa' => 'bg-red-50 text-red-700 border border-red-200',
        'sakit' => 'bg-purple-50 text-purple-700 border border-purple-200',
        'izin' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
    ];
@endphp

@if(session('status'))
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('status') }}
    </div>
@endif

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <form method="GET" action="{{ route('web.attendances.history') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label for="date" class="block text-xs font-semibold text-slate-500 uppercase">Tanggal</label>
            <input type="date" name="date" id="date" value="{{ request('date') }}"
                   class="mt-1 w-48 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        </div>
        <div>
            <label for="class" class="block text-xs font-semibold text-slate-500 uppercase">Kelas</label>
            <select name="class" id="class"
                    class="mt-1 w-44 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Semua</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((string) request('class') === (string) $classroom->id)>{{ $classroom->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="journal" class="block text-xs font-semibold text-slate-500 uppercase">Jurnal</label>
            <select name="journal" id="journal"
                    class="mt-1 w-64 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Semua</option>
                @foreach($journals as $journal)
                    @php
                        $label = $journal->tanggal->translatedFormat('d F Y') . ' • ' . $journal->mata_pelajaran;
                    @endphp
                    <option value="{{ $journal->id }}" @selected((string) request('journal') === (string) $journal->id)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-xs font-semibold text-slate-500 uppercase">Status</label>
            <select name="status" id="status"
                    class="mt-1 w-40 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Semua</option>
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Filter</button>
        <a href="{{ route('web.attendances.history') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100">Reset</a>
    </form>

    <a href="{{ route('web.attendances.index') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
        Kembali ke Pencatatan
    </a>
</div>

<div class="overflow-x-auto rounded-xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Siswa</th>
                <th class="px-4 py-3">Jurnal</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse($attendances as $attendance)
            <tr>
                <td class="px-4 py-3 text-slate-600">{{ $attendance->date->translatedFormat('d F Y') }}</td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $attendance->student->name }} <span class="text-xs text-slate-500">({{ $attendance->student->classroom?->name ?? '-' }})</span></td>
                <td class="px-4 py-3 text-slate-600">
                    @if($attendance->teachingJournal)
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 5.75h14.5m-14.5 4.5h14.5m-14.5 4.5H12"/></svg>
                            {{ $attendance->teachingJournal->subject->name ?? $attendance->teachingJournal->mata_pelajaran }}
                        </span>
                    @else
                        <span class="text-xs text-slate-400">Tidak terhubung</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$attendance->status] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ ucfirst($attendance->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <form method="POST" action="{{ route('web.attendances.destroy', $attendance) }}" onsubmit="return confirm('Hapus data absensi ini?');" class="inline-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data absensi untuk filter yang dipilih.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $attendances->links() }}
</div>
@endsection
