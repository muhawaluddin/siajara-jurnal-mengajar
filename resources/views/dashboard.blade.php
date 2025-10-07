@extends('layouts.app')

@section('title', 'Dashboard | ' . config('app.name'))

@section('page-title', null)
@section('page-subtitle', null)

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin();
    $currentMonth = now()->month;
    $currentYear = now()->year;
    $gridCols = $isAdmin ? 'md:grid-cols-3' : 'md:grid-cols-2';

    $studentCount = $isAdmin
        ? \App\Models\Student::count()
        : \App\Models\Attendance::query()
            ->whereHas('teachingJournal', fn ($query) => $query->where('guru_id', $user?->id))
            ->distinct('student_id')
            ->count();

    if (! $isAdmin && $studentCount === 0) {
        $studentCount = \App\Models\Student::query()
            ->whereHas('attendances.teachingJournal', fn ($query) => $query->where('guru_id', $user?->id))
            ->distinct('id')
            ->count();
    }

    $journalThisMonth = $isAdmin
        ? \App\Models\TeachingJournal::whereYear('tanggal', $currentYear)->whereMonth('tanggal', $currentMonth)->count()
        : \App\Models\TeachingJournal::where('guru_id', $user?->id)
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth)
            ->count();

    $attendanceTodayQuery = \App\Models\Attendance::whereDate('date', today());
    if (! $isAdmin) {
        $attendanceTodayQuery->whereHas('teachingJournal', fn ($query) => $query->where('guru_id', $user?->id));
    }
    $attendanceToday = $attendanceTodayQuery->count();

    $perGuruDurations = \App\Services\MonthlyReportService::resolveForDashboard($currentYear, $currentMonth);
    if (! $isAdmin) {
        $perGuruDurations = $perGuruDurations
            ->filter(fn ($row) => ($row['guru']['id'] ?? null) === $user?->id)
            ->values();
    }
@endphp

<div class="space-y-6">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 p-6 text-white shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-100">Selamat datang kembali</p>
                <h2 class="text-3xl font-semibold">{{ $user?->name }}, semoga harimu menyenangkan!</h2>
                <p class="text-sm text-emerald-100/90 max-w-2xl">Gunakan panel ini untuk memonitor data siswa, absensi harian, serta jurnal mengajar. Kami sudah menyiapkan pintasan cepat agar pekerjaanmu lebih efisien.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-1 text-sm font-medium backdrop-blur">
                {{ $isAdmin ? 'Administrator' : 'Guru' }}
            </span>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A14.918 14.918 0 0 0 3 9c1.5 4.5 5.25 7.5 9 7.5S19.5 13.5 21 9a14.918 14.918 0 0 0-9-2.958ZM12 6.042V9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a4.5 4.5 0 0 1 4.5 4.5v.75H7.5v-.75a4.5 4.5 0 0 1 4.5-4.5Z"/></svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Siswa</p>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($studentCount) }}</p>
                <p class="text-xs text-slate-500">Siswa aktif dalam sistem</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h12M3 9h12M3 13.5h7.5M16.5 4.5l4.5 2.25M16.5 9l4.5 2.25M16.5 13.5l4.5 2.25M16.5 18l4.5 2.25"/></svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jurnal Bulan Ini</p>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($journalThisMonth) }}</p>
                <p class="text-xs text-slate-500">Pertemuan yang tercatat</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12 4.5 4.5 10.5-10.5"/></svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Absensi Hari Ini</p>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($attendanceToday) }}</p>
                <p class="text-xs text-slate-500">Catatan kehadiran tercatat</p>
            </div>
        </div>
    </div>

    @if($perGuruDurations->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Durasi Mengajar {{ $isAdmin ? 'Guru' : 'Saya' }}</h3>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Periode {{ now()->translatedFormat('F Y') }}</p>
                </div>
                @if($isAdmin)
                    <span class="text-xs text-slate-400">Total guru: {{ $perGuruDurations->count() }}</span>
                @endif
            </div>
            <div class="mt-5 space-y-4">
                @php $maxDuration = max($perGuruDurations->pluck('total_durasi_menit')->max() ?? 0, 1); @endphp
                @foreach($perGuruDurations as $row)
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17.25v1.5M19.5 3.75l-15 9m12-9H21v4.5m-9 5.25H6.75m5.25 4.5H4.5"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-800">{{ $row['guru']['name'] ?? 'Guru' }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($row['total_pertemuan']) }} pertemuan</p>
                            <div class="mt-1 h-2 w-full rounded-full bg-slate-200">
                                @php
                                    $percent = ($row['total_durasi_menit'] / $maxDuration) * 100;
                                @endphp
                                <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-slate-900">{{ sprintf('%02dj %02dm', intdiv($row['total_durasi_menit'], 60), $row['total_durasi_menit'] % 60) }}</p>
                            <p class="text-xs text-slate-500">Total jam mengajar</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-4 {{ $gridCols }}">
        <a href="{{ route('web.attendances.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h-3a.75.75 0 0 0-.75.75v12a.75.75 0 0 0 .75.75H18a.75.75 0 0 0 .75-.75v-12a.75.75 0 0 0-.75-.75h-3M8.25 6.75V5.25A2.25 2.25 0 0 1 10.5 3h3a2.25 2.25 0 0 1 2.25 2.25v1.5M8.25 6.75h7.5"/></svg>
                </span>
                <div class="space-y-1">
                    <h4 class="text-base font-semibold text-slate-900">Absensi Harian</h4>
                    <p class="text-sm text-slate-500">Catat kehadiran seluruh siswa langsung dari satu layar dengan filter kelas.</p>
                </div>
            </div>
        </a>

        <a href="{{ route('web.teaching-journals.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.25 2.25 0 1 1 3.182 3.182L9 18.75 4.5 19.5l.75-4.5 11.612-10.513Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 6 15 10.5"/></svg>
                </span>
                <div class="space-y-1">
                    <h4 class="text-base font-semibold text-slate-900">Jurnal Mengajar</h4>
                    <p class="text-sm text-slate-500">Lengkapi catatan pembelajaran, topik, dan refleksi dari setiap pertemuan.</p>
                </div>
            </div>
        </a>

        @if($isAdmin)
            <a href="{{ route('admin.classrooms.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6h15m-15 6h9m-9 6h6"/></svg>
                    </span>
                    <div class="space-y-1">
                        <h4 class="text-base font-semibold text-slate-900">Master Data</h4>
                        <p class="text-sm text-slate-500">Atur kelas, mata pelajaran, dan akun guru untuk menjaga data tetap rapih.</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.reports.index') }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-md">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 19.5h18M5.25 4.5v15M18.75 4.5v15M9 8.25h6m-6 4.5h6"/></svg>
                    </span>
                    <div class="space-y-1">
                        <h4 class="text-base font-semibold text-slate-900">Laporan Bulanan</h4>
                        <p class="text-sm text-slate-500">Unduh ringkasan PDF/Excel untuk absensi dan jurnal dalam satu klik.</p>
                    </div>
                </div>
            </a>
        @endif
    </div>
</div>
@endsection
