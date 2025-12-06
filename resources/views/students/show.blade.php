@extends('layouts.app')

@section('title', 'Profil Siswa | ' . config('app.name'))
@section('page-title', 'Profil Siswa')
@section('page-subtitle', 'Detail identitas dan kehadiran siswa sesuai standar data sekolah.')

@php
    $statusStyles = [
        'hadir' => 'bg-green-50 text-green-700 border border-green-200',
        'alpa' => 'bg-red-50 text-red-700 border border-red-200',
        'sakit' => 'bg-purple-50 text-purple-700 border border-purple-200',
        'izin' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'pulang izin' => 'bg-amber-50 text-amber-700 border border-amber-200',
        'pulang sakit' => 'bg-blue-50 text-blue-700 border border-blue-200',
    ];

    $totals = $summary['totals'] ?? [];
    $presencePercentage = $summary['presence_percentage'] ?? 0;
    $perSubject = $summary['per_subject'] ?? [];
    $recentRecords = $summary['recent_records'] ?? [];
@endphp

@section('content')
<div class="mb-6 flex flex-wrap items-center gap-3">
    <a href="{{ route('admin.students.index') }}"
       class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
        Kembali
    </a>
    <a href="{{ route('admin.students.edit', $student) }}"
       class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
        Ubah Data
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 font-bold text-lg">
                {{ strtoupper(substr($student->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500">Nama Lengkap</p>
                <h3 class="text-lg font-semibold text-slate-900">{{ $student->name }}</h3>
            </div>
        </div>

        <dl class="mt-5 space-y-3 text-sm text-slate-700">
            <div class="flex items-start justify-between">
                <dt class="text-slate-500">NIS</dt>
                <dd class="font-semibold text-slate-900">{{ $student->nis ?? '—' }}</dd>
            </div>
            <div class="flex items-start justify-between">
                <dt class="text-slate-500">Kelas</dt>
                <dd class="font-semibold text-slate-900">{{ $student->classroom?->name ?? '—' }}</dd>
            </div>
            <div class="flex items-start justify-between">
                <dt class="text-slate-500">Total Catatan Absensi</dt>
                <dd class="font-semibold text-slate-900">{{ $totals['total_records'] ?? 0 }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-600 to-emerald-500 p-6 text-white shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-50">Persentase Hadir</p>
        <div class="mt-4 flex items-end gap-2">
            <p class="text-5xl font-bold leading-none">{{ number_format($presencePercentage, 1) }}<span class="text-xl font-semibold">%</span></p>
            <span class="text-xs text-emerald-50">dari {{ $totals['total_records'] ?? 0 }} catatan absensi</span>
        </div>
        <div class="mt-5 h-2 w-full rounded-full bg-emerald-100/50">
            <div class="h-2 rounded-full bg-white/90" style="width: {{ min(100, $presencePercentage) }}%"></div>
        </div>
        <div class="mt-5 grid grid-cols-3 gap-2 text-xs">
            <div class="rounded-lg border border-emerald-300/60 bg-emerald-50/10 px-3 py-2">
                <p class="text-emerald-100">Hadir</p>
                <p class="text-base font-semibold text-white">{{ $totals['hadir'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-emerald-300/60 bg-emerald-50/10 px-3 py-2">
                <p class="text-emerald-100">Izin</p>
                <p class="text-base font-semibold text-white">{{ $totals['izin'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-emerald-300/60 bg-emerald-50/10 px-3 py-2">
                <p class="text-emerald-100">Alpa</p>
                <p class="text-base font-semibold text-white">{{ $totals['alpa'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Catatan Terbaru</p>
        <div class="mt-4 space-y-2">
            @forelse($recentRecords as $record)
                @php
                    $parsedDate = $record['date'] ? \Illuminate\Support\Carbon::parse($record['date']) : null;
                @endphp
                <div class="flex flex-col rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold text-slate-800">{{ $parsedDate?->translatedFormat('d M Y') ?? '-' }}</span>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$record['status']] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">{{ ucwords($record['status']) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Mapel: {{ $record['subject'] }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-500">Belum ada catatan absensi.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
        <div>
            <h3 class="text-base font-semibold text-slate-900">Persentase Kehadiran per Mapel</h3>
            <p class="text-xs text-slate-500 mt-1">Ringkasan per mata pelajaran untuk siswa ini.</p>
        </div>
        <span class="text-xs font-semibold text-emerald-700">{{ count($perSubject) }} mapel</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Mata Pelajaran</th>
                    <th class="px-4 py-3 text-center">Hadir</th>
                    <th class="px-4 py-3 text-center">Izin</th>
                    <th class="px-4 py-3 text-center">Pulang Izin</th>
                    <th class="px-4 py-3 text-center">Sakit</th>
                    <th class="px-4 py-3 text-center">Pulang Sakit</th>
                    <th class="px-4 py-3 text-center">Alpa</th>
                    <th class="px-4 py-3 text-center">Total</th>
                    <th class="px-4 py-3 text-center">Persentase Hadir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($perSubject as $row)
                <tr class="bg-white hover:bg-emerald-50/40">
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['subject']['name'] ?? 'Tidak diketahui' }}</td>
                    <td class="px-4 py-3 text-center text-green-700 font-semibold">{{ $row['totals']['hadir'] }}</td>
                    <td class="px-4 py-3 text-center text-yellow-700 font-semibold">{{ $row['totals']['izin'] }}</td>
                    <td class="px-4 py-3 text-center text-amber-700 font-semibold">{{ $row['totals']['pulang izin'] }}</td>
                    <td class="px-4 py-3 text-center text-purple-700 font-semibold">{{ $row['totals']['sakit'] }}</td>
                    <td class="px-4 py-3 text-center text-blue-700 font-semibold">{{ $row['totals']['pulang sakit'] }}</td>
                    <td class="px-4 py-3 text-center text-red-700 font-semibold">{{ $row['totals']['alpa'] }}</td>
                    <td class="px-4 py-3 text-center text-slate-700 font-semibold">{{ $row['totals']['total_records'] }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-emerald-700">{{ number_format($row['presence_percentage'], 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada catatan absensi untuk siswa ini.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
