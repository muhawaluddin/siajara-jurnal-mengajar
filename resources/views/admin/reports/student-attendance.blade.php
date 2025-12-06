@extends('layouts.app')

@section('title', 'Rekap Kehadiran Siswa | ' . config('app.name'))

@section('page-title', 'Rekap Kehadiran Siswa')
@section('page-subtitle', 'Lihat total dan persentase hadir per siswa untuk satu atau semua mata pelajaran.')

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
<form method="GET" action="{{ route('admin.student-attendance.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div>
        <label for="student_id" class="block text-xs font-semibold text-emerald-600 uppercase">Siswa</label>
        <select name="student_id" id="student_id" class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Pilih siswa</option>
            @foreach($students as $option)
                <option value="{{ $option->id }}" @selected($selectedStudentId === $option->id)>{{ $option->name }} {{ $option->classroom?->name ? '(' . $option->classroom->name . ')' : '' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="subject_id" class="block text-xs font-semibold text-emerald-600 uppercase">Mata Pelajaran</label>
        <select name="subject_id" id="subject_id" class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Semua mapel</option>
            @foreach($subjects as $option)
                <option value="{{ $option->id }}" @selected($selectedSubjectId === $option->id)>{{ $option->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="start_date" class="block text-xs font-semibold text-emerald-600 uppercase">Tanggal Mulai</label>
        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] }}"
               class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>
    <div>
        <label for="end_date" class="block text-xs font-semibold text-emerald-600 uppercase">Tanggal Akhir</label>
        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] }}"
               class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>
    <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Terapkan Filter</button>
        <a href="{{ route('admin.student-attendance.index') }}" class="inline-flex items-center rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Reset</a>
        @if($student)
            <span class="text-xs text-slate-500">Siswa: <strong class="text-emerald-700">{{ $student->name }}</strong> {{ $student->classroom?->name ? '• ' . $student->classroom->name : '' }}</span>
        @else
            <span class="text-xs text-slate-500">Pilih siswa untuk menampilkan rekap.</span>
        @endif
    </div>
</form>

@if(! $student)
    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-600">
        Pilih siswa terlebih dahulu untuk melihat rekap kehadiran.
    </div>
@else
    <div class="grid gap-4 lg:grid-cols-3 mb-6">
        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-600 to-emerald-500 p-5 text-white shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-50">Persentase Hadir</p>
            <div class="mt-3 flex items-end gap-2">
                <p class="text-4xl font-bold leading-none">{{ number_format($presencePercentage, 1) }}<span class="text-lg font-semibold">%</span></p>
                <span class="text-xs text-emerald-50">dari {{ $totals['total_records'] ?? 0 }} catatan</span>
            </div>
            <div class="mt-4 h-2 w-full rounded-full bg-emerald-100/50">
                <div class="h-2 rounded-full bg-white/90" style="width: {{ min(100, $presencePercentage) }}%"></div>
            </div>
            @if($subject)
                <p class="mt-2 text-xs text-emerald-50">Mapel: {{ $subject->name }}</p>
            @endif
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Ringkasan Status</p>
            <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-lg border border-green-200 bg-green-50/70 px-3 py-2">
                    <dt class="text-xs text-green-700 uppercase">Hadir</dt>
                    <dd class="text-lg font-semibold text-green-800">{{ $totals['hadir'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50/70 px-3 py-2">
                    <dt class="text-xs text-red-700 uppercase">Alpa</dt>
                    <dd class="text-lg font-semibold text-red-800">{{ $totals['alpa'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50/70 px-3 py-2">
                    <dt class="text-xs text-yellow-700 uppercase">Izin</dt>
                    <dd class="text-lg font-semibold text-yellow-800">{{ $totals['izin'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50/70 px-3 py-2">
                    <dt class="text-xs text-purple-700 uppercase">Sakit</dt>
                    <dd class="text-lg font-semibold text-purple-800">{{ $totals['sakit'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50/70 px-3 py-2">
                    <dt class="text-xs text-amber-700 uppercase">Pulang Izin</dt>
                    <dd class="text-lg font-semibold text-amber-800">{{ $totals['pulang izin'] ?? 0 }}</dd>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50/70 px-3 py-2">
                    <dt class="text-xs text-blue-700 uppercase">Pulang Sakit</dt>
                    <dd class="text-lg font-semibold text-blue-800">{{ $totals['pulang sakit'] ?? 0 }}</dd>
                </div>
            </dl>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Catatan Terbaru</p>
            <div class="mt-3 space-y-2">
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
                    <p class="text-xs text-slate-500">Belum ada catatan kehadiran pada filter ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Persentase Hadir per Mapel</h3>
                <p class="text-xs text-slate-500 mt-1">Hitungan berdasarkan catatan absensi pada filter saat ini.</p>
            </div>
            <span class="text-xs text-emerald-700 font-semibold">{{ count($perSubject) }} mapel</span>
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
                        <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada catatan absensi untuk filter ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
