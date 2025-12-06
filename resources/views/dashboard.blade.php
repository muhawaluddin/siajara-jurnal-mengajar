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
    $monthLabel = now()->translatedFormat('F Y');
    $previousMonthDate = now()->subMonth();
    $previousMonth = $previousMonthDate->month;
    $previousYear = $previousMonthDate->year;
    $previousMonthLabel = $previousMonthDate->translatedFormat('F Y');

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

    $perGuruDurationsPrev = \App\Services\MonthlyReportService::resolveForDashboard($previousYear, $previousMonth);
    if (! $isAdmin) {
        $perGuruDurationsPrev = $perGuruDurationsPrev
            ->filter(fn ($row) => ($row['guru']['id'] ?? null) === $user?->id)
            ->values();
    }

    $teacherSummary = null;
    $teacherDurationMinutes = 0;
    $teacherMeetings = 0;
    $teacherSummaryPrev = null;
    $teacherDurationMinutesPrev = 0;
    $teacherMeetingsPrev = 0;

    if (! $isAdmin) {
        $teacherSummary = $perGuruDurations->first();
        $teacherDurationMinutes = $teacherSummary['total_durasi_menit'] ?? 0;
        $teacherMeetings = $teacherSummary['total_pertemuan'] ?? $journalThisMonth;

        $teacherSummaryPrev = $perGuruDurationsPrev->first();
        $teacherDurationMinutesPrev = $teacherSummaryPrev['total_durasi_menit'] ?? 0;
        $teacherMeetingsPrev = $teacherSummaryPrev['total_pertemuan'] ?? 0;
    }

    $totalDurationMinutes = $perGuruDurations->sum('total_durasi_menit');
    $totalMeetingsAll = $perGuruDurations->sum('total_pertemuan');
    $displayDurationMinutes = $isAdmin ? $totalDurationMinutes : $teacherDurationMinutes;
    $displayMeetings = $isAdmin ? $totalMeetingsAll : $teacherMeetings;
    $displayDurationHours = intdiv($displayDurationMinutes, 60);
    $displayDurationRemainingMinutes = $displayDurationMinutes % 60;

    $displayDurationMinutesPrev = $isAdmin
        ? $perGuruDurationsPrev->sum('total_durasi_menit')
        : $teacherDurationMinutesPrev;
    $displayMeetingsPrev = $isAdmin
        ? $perGuruDurationsPrev->sum('total_pertemuan')
        : $teacherMeetingsPrev;
    $chartMaxMinutes = max($displayDurationMinutes, $displayDurationMinutesPrev, 1);

    $heroGradient = $isAdmin
        ? 'from-emerald-600 via-teal-600 to-sky-500'
        : 'from-sky-600 via-blue-600 to-indigo-500';
    $modeLabel = $isAdmin ? 'Panel Administrator' : 'Panel Guru';

    $guruChartData = $isAdmin
        ? $perGuruDurations->sortByDesc('total_durasi_menit')->take(7)
        : collect();
    $maxGuruMinutes = max($guruChartData->pluck('total_durasi_menit')->max() ?? 0, 1);
@endphp

<div class="space-y-8">
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 text-white shadow-xl">
        <div class="absolute inset-0 bg-gradient-to-br {{ $heroGradient }}"></div>
        <div class="absolute -left-14 -top-10 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -right-20 top-8 h-56 w-56 rounded-full bg-white/15 blur-3xl"></div>
        <div class="relative grid gap-6 p-6 md:grid-cols-3 md:items-center md:p-8">
            <div class="space-y-3 md:col-span-2">
                <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.3em] text-white/70">
                    <span>Selamat datang kembali</span>
                    <span class="hidden h-4 w-px bg-white/30 md:inline"></span>
                    <span class="hidden md:inline">{{ now()->translatedFormat('d F Y') }}</span>
                </div>
                <h2 class="text-3xl font-semibold md:text-4xl">{{ $user?->name }}, semoga harimu menyenangkan!</h2>
                <p class="max-w-3xl text-sm text-white/80">Gunakan panel ini untuk memonitor data siswa, absensi harian, serta jurnal mengajar. Kami menyiapkan pintasan cepat agar pekerjaanmu lebih efisien.</p>
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>{{ $monthLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide backdrop-blur">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"/></svg>
                        {{ $modeLabel }}
                    </span>
                </div>
            </div>
            <div class="flex flex-col gap-3 self-start justify-self-end text-left md:text-right">
                <span class="inline-flex items-center gap-2 self-start rounded-full bg-white/15 px-4 py-2 text-sm font-medium backdrop-blur md:self-end">
                    <span class="h-2 w-2 rounded-full bg-emerald-200"></span>{{ $isAdmin ? 'Administrator' : 'Guru' }}
                </span>
                <div class="rounded-2xl bg-white/10 px-5 py-4 shadow-inner backdrop-blur">
                    <p class="text-xs uppercase tracking-wide text-white/70">Jam mengajar bulan ini</p>
                    <p class="text-2xl font-semibold leading-tight">
                        {{ sprintf('%02dj %02dm', $displayDurationHours, $displayDurationRemainingMinutes) }}
                    </p>
                    <p class="text-xs text-white/70">{{ number_format($displayMeetings) }} pertemuan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-emerald-100/60 blur-2xl transition-all group-hover:scale-110"></div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total jam mengajar</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ sprintf('%02dj %02dm', $displayDurationHours, $displayDurationRemainingMinutes) }}</p>
                    <p class="text-xs text-slate-500">Periode {{ $monthLabel }}</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.5 2"/></svg>
                </span>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-teal-100/60 blur-2xl transition-all group-hover:scale-110"></div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah mengajar</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($displayMeetings) }}</p>
                    <p class="text-xs text-slate-500">Pertemuan yang tercatat</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-100 text-teal-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h12M3 9h12M3 13.5h7.5M16.5 4.5l4.5 2.25M16.5 9l4.5 2.25M16.5 13.5l4.5 2.25M16.5 18l4.5 2.25"/></svg>
                </span>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-sky-100/60 blur-2xl transition-all group-hover:scale-110"></div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total siswa</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($studentCount) }}</p>
                    <p class="text-xs text-slate-500">Siswa aktif dalam sistem</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A14.918 14.918 0 0 0 3 9c1.5 4.5 5.25 7.5 9 7.5S19.5 13.5 21 9a14.918 14.918 0 0 0-9-2.958ZM12 6.042V9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a4.5 4.5 0 0 1 4.5 4.5v.75H7.5v-.75a4.5 4.5 0 0 1 4.5-4.5Z"/></svg>
                </span>
            </div>
        </div>

        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-lime-100/70 blur-2xl transition-all group-hover:scale-110"></div>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Absensi hari ini</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($attendanceToday) }}</p>
                    <p class="text-xs text-slate-500">Catatan kehadiran tercatat</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-lime-100 text-lime-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12 4.5 4.5 10.5-10.5"/></svg>
                </span>
            </div>
        </div>
    </div>

    @if($isAdmin ? $perGuruDurations->isNotEmpty() : true)
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Perbandingan bulan ini vs bulan lalu</h3>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Jam mengajar & pertemuan</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $monthLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>{{ $previousMonthLabel }}
                    </span>
                </div>
            </div>

            @php
                $bars = [
                    [
                        'label' => $previousMonthLabel,
                        'minutes' => $displayDurationMinutesPrev,
                        'meetings' => $displayMeetingsPrev,
                        'gradient' => 'from-slate-300 to-slate-500',
                    ],
                    [
                        'label' => $monthLabel,
                        'minutes' => $displayDurationMinutes,
                        'meetings' => $displayMeetings,
                        'gradient' => 'from-emerald-400 to-teal-500',
                    ],
                ];
            @endphp

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="space-y-4">
                    @foreach($bars as $bar)
                        @php
                            $hours = intdiv($bar['minutes'], 60);
                            $minutes = $bar['minutes'] % 60;
                            $percent = ($bar['minutes'] / $chartMaxMinutes) * 100;
                        @endphp
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 shadow-[0_4px_12px_-10px_rgba(15,23,42,0.35)]">
                            <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                                <span>{{ $bar['label'] }}</span>
                                <span class="text-xs font-medium text-slate-500">{{ number_format($bar['meetings']) }} pertemuan</span>
                            </div>
                            <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-200">
                                <div class="h-3 rounded-full bg-gradient-to-r {{ $bar['gradient'] }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ sprintf('%02dj %02dm', $hours, $minutes) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-col justify-center gap-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-800 p-5 text-white shadow-inner">
                    <p class="text-xs uppercase tracking-wide text-emerald-100/70">Insight singkat</p>
                    @php
                        $deltaMinutes = $displayDurationMinutes - $displayDurationMinutesPrev;
                        $deltaMeetings = $displayMeetings - $displayMeetingsPrev;
                        $deltaSign = $deltaMinutes >= 0 ? '+' : '-';
                        $deltaHoursAbs = intdiv(abs($deltaMinutes), 60);
                        $deltaMinutesAbs = abs($deltaMinutes) % 60;
                    @endphp
                    <div class="text-3xl font-semibold">
                        {{ "{$deltaSign}{$deltaHoursAbs} jam {$deltaSign}{$deltaMinutesAbs} menit" }}
                    </div>
                    <p class="text-sm text-emerald-100/90">Perubahan jam mengajar dibandingkan bulan sebelumnya ({{ $deltaMeetings >= 0 ? '+' : '' }}{{ number_format($deltaMeetings) }} pertemuan).</p>
                    <div class="flex gap-3 text-sm text-emerald-50/90">
                        <div class="flex-1 rounded-lg bg-white/10 px-3 py-2">
                            <p class="text-[11px] uppercase tracking-wide text-emerald-100/70">Bulan lalu</p>
                            <p class="text-lg font-semibold text-white">{{ sprintf('%02dj %02dm', intdiv($displayDurationMinutesPrev, 60), $displayDurationMinutesPrev % 60) }}</p>
                            <p class="text-xs text-emerald-100/80">{{ number_format($displayMeetingsPrev) }} pertemuan</p>
                        </div>
                        <div class="flex-1 rounded-lg bg-white/10 px-3 py-2">
                            <p class="text-[11px] uppercase tracking-wide text-emerald-100/70">Bulan ini</p>
                            <p class="text-lg font-semibold text-white">{{ sprintf('%02dj %02dm', $displayDurationHours, $displayDurationRemainingMinutes) }}</p>
                            <p class="text-xs text-emerald-100/80">{{ number_format($displayMeetings) }} pertemuan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isAdmin)
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Grafik jam mengajar per guru</h3>
                        <p class="text-xs uppercase tracking-wide text-slate-500">Top {{ $guruChartData->count() }} guru - {{ $monthLabel }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Admin only</span>
                </div>
                <div class="mt-6">
                    @if($guruChartData->isEmpty())
                        <div class="flex items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/70 p-6 text-sm text-slate-500">
                            Belum ada data jam mengajar yang tercatat bulan ini.
                        </div>
                    @else
                        <div class="flex h-72 items-end gap-4 rounded-xl border border-slate-100 bg-gradient-to-b from-slate-50 to-white px-4 pb-4 pt-6 shadow-inner">
                            @foreach($guruChartData as $guru)
                                @php
                                    $minutes = $guru['total_durasi_menit'];
                                    $barHeight = max(($minutes / $maxGuruMinutes) * 100, 6);
                                    $hours = intdiv($minutes, 60);
                                    $remMinutes = $minutes % 60;
                                @endphp
                                <div class="flex-1 space-y-3 text-center">
                                    <div class="relative mx-auto flex h-full w-full flex-col justify-end">
                                        <div class="mx-auto h-full w-14 rounded-full bg-slate-100">
                                            <div class="h-full">
                                                <div class="mx-auto flex h-full w-14 items-end justify-center">
                                                    <div class="relative w-10 rounded-full bg-gradient-to-t from-emerald-500 to-teal-400 shadow-lg" style="height: {{ $barHeight }}%">
                                                        <span class="absolute -top-7 left-1/2 -translate-x-1/2 rounded-full bg-white px-2 py-1 text-[11px] font-semibold text-emerald-700 shadow-sm">{{ sprintf('%02dj', $hours) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-1 text-xs">
                                        <p class="font-semibold text-slate-800">{{ \Illuminate\Support\Str::limit($guru['guru']['name'] ?? 'Guru', 14) }}</p>
                                        <p class="text-slate-500">{{ sprintf('%02dj %02dm', $hours, $remMinutes) }}</p>
                                        <span class="inline-flex items-center justify-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12 9 16.5l10.5-9"/></svg>
                                            {{ number_format($guru['total_pertemuan']) }}x
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-emerald-700/80">Fokus admin</p>
                        <h4 class="text-lg font-semibold text-emerald-900">Pantau performa guru</h4>
                        <p class="text-sm text-emerald-800/80">Gunakan grafik kiri untuk melihat guru paling aktif dan temukan kelas yang membutuhkan perhatian.</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-emerald-600 shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15m-15 4.5h15m-15 4.5H12"/></svg>
                    </span>
                </div>
                <ul class="mt-4 space-y-2 text-sm text-emerald-900/90">
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-emerald-600"></span>Identifikasi guru dengan jam terendah untuk coaching.</li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-teal-500"></span>Pastikan pertemuan tercatat konsisten setiap minggu.</li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>Gunakan menu laporan untuk membagikan rekap ke kepala sekolah.</li>
                </ul>
            </div>
        </div>
    @endif

    @if(! $isAdmin)
        @php
            $personalGrowth = $displayDurationMinutesPrev > 0 ? (($displayDurationMinutes - $displayDurationMinutesPrev) / $displayDurationMinutesPrev) * 100 : 100;
            $personalGrowth = round($personalGrowth, 1);
            $focusColor = $personalGrowth >= 0 ? 'from-sky-500 to-indigo-500' : 'from-amber-500 to-orange-500';
            $personalPercent = min(100, ($displayDurationMinutes / max($displayDurationMinutesPrev ?: 60, 60)) * 100);
        @endphp
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Progress mengajar saya</h3>
                        <p class="text-xs uppercase tracking-wide text-slate-500">Dibandingkan bulan lalu</p>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">Mode guru</span>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jam mengajar</p>
                        <div class="mt-2 flex items-end gap-3">
                            <div class="flex-1">
                                <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-3 rounded-full bg-gradient-to-r from-sky-500 to-indigo-500" style="width: {{ $personalPercent }}%"></div>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">Perkiraan target tercapai {{ number_format($personalPercent, 0) }}%</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-semibold text-slate-900">{{ sprintf('%02dj %02dm', $displayDurationHours, $displayDurationRemainingMinutes) }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($displayMeetings) }} pertemuan</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-gradient-to-br {{ $focusColor }} p-4 text-white shadow-inner">
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Perubahan bulan ini</p>
                        <div class="mt-2 text-3xl font-semibold">
                            {{ $personalGrowth >= 0 ? '+' : '' }}{{ $personalGrowth }}%
                        </div>
                        <p class="mt-1 text-sm text-white/80">Dibandingkan bulan sebelumnya ({{ $previousMonthLabel }}).</p>
                        <div class="mt-3 flex items-center gap-3 rounded-lg bg-white/10 px-3 py-2 text-sm">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12 4.5 4.5 10.5-10.5"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold leading-tight text-white">Fokuskan pencatatan</p>
                                <p class="text-xs text-white/80">Lengkapi jurnal tepat setelah kelas selesai agar grafik terus naik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-sky-50 p-5 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-sky-700/80">Agenda guru</p>
                <h4 class="mt-1 text-lg font-semibold text-slate-900">Tindakan cepat hari ini</h4>
                <ul class="mt-4 space-y-3 text-sm text-slate-700">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 h-2 w-2 rounded-full bg-sky-600"></span>
                        Lengkapi absensi kelas terakhir ({{ number_format($attendanceToday) }} sudah tercatat).
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 h-2 w-2 rounded-full bg-indigo-500"></span>
                        Review jurnal minggu ini dan tambahkan refleksi singkat.
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 h-2 w-2 rounded-full bg-sky-400"></span>
                        Rencanakan materi pertemuan berikutnya di menu Jurnal Mengajar.
                    </li>
                </ul>
            </div>
        </div>
    @endif

    @if($perGuruDurations->isNotEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Ringkasan durasi mengajar {{ $isAdmin ? 'guru' : 'saya' }}</h3>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Periode {{ $monthLabel }}</p>
                </div>
                @if($isAdmin)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Total guru: {{ $perGuruDurations->count() }}</span>
                @endif
            </div>
            <div class="mt-5 space-y-4">
                @php $maxDuration = max($perGuruDurations->pluck('total_durasi_menit')->max() ?? 0, 1); @endphp
                @foreach($perGuruDurations as $row)
                    <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-slate-50/70 p-4 shadow-[0_4px_12px_-8px_rgba(15,23,42,0.3)]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17.25v1.5M19.5 3.75l-15 9m12-9H21v4.5m-9 5.25H6.75m5.25 4.5H4.5"/></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-slate-800">{{ $row['guru']['name'] ?? 'Guru' }}</p>
                                <span class="rounded-full bg-white px-2 py-1 text-[11px] font-medium text-emerald-700 shadow-sm">{{ number_format($row['total_pertemuan']) }}x pertemuan</span>
                            </div>
                            <div class="mt-2 h-2 w-full rounded-full bg-slate-200">
                                @php
                                    $percent = ($row['total_durasi_menit'] / $maxDuration) * 100;
                                @endphp
                                <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold text-slate-900">{{ sprintf('%02dj %02dm', intdiv($row['total_durasi_menit'], 60), $row['total_durasi_menit'] % 60) }}</p>
                            <p class="text-xs text-slate-500">Total jam</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-4 {{ $gridCols }}">
        <a href="{{ route('web.attendances.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-emerald-100/70 blur-2xl transition-all group-hover:scale-110"></div>
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

        <a href="{{ route('web.teaching-journals.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
            <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-teal-100/70 blur-2xl transition-all group-hover:scale-110"></div>
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
            <a href="{{ route('admin.classrooms.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg">
                <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-amber-100/70 blur-2xl transition-all group-hover:scale-110"></div>
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

            <a href="{{ route('admin.reports.index') }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                <div class="absolute -right-10 -top-10 h-24 w-24 rounded-full bg-emerald-100/70 blur-2xl transition-all group-hover:scale-110"></div>
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
