@extends('layouts.app')

@section('title', 'Laporan Nilai Siswa | ' . config('app.name'))
@section('page-title', 'Laporan Nilai Siswa')
@section('page-subtitle', 'Pantau nilai siswa di semua mata pelajaran.')

@section('content')
<div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
    <form method="GET" action="{{ route('admin.grades.index') }}" class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label for="classroom_id" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kelas</label>
                <select name="classroom_id" id="classroom_id" class="mt-1 w-48 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                    <option value="">Semua Kelas</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected((int) request('classroom_id') === $classroom->id)>{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="subject_id" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Mata Pelajaran</label>
                <select name="subject_id" id="subject_id" class="mt-1 w-48 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((int) request('subject_id') === $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Siswa</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama siswa"
                       class="mt-1 w-52 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Terapkan</button>
            <a href="{{ route('admin.grades.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">Reset</a>
        </div>
    </form>
</div>

<div class="space-y-4">
    @forelse($students as $student)
        @php
            $scoresBySubject = $student->assessmentScores
                ->filter(fn($score) => $score->assessment && $score->assessment->subject)
                ->groupBy(fn($score) => $score->assessment->subject->name);
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="text-base font-semibold text-slate-900">{{ $student->name }}</p>
                    <p class="text-sm text-slate-500">Kelas {{ $student->classroom?->name ?? '-' }}</p>
                </div>
                <div class="rounded-full bg-emerald-50 px-4 py-1 text-sm font-semibold text-emerald-700">
                    {{ $student->assessmentScores->count() }} penilaian
                </div>
            </div>

            @if($scoresBySubject->isEmpty())
                <div class="px-5 py-4 text-sm text-slate-500">Belum ada nilai untuk siswa ini.</div>
            @else
                <div class="grid gap-4 px-5 py-4 md:grid-cols-2">
                    @foreach($scoresBySubject as $subjectName => $scores)
                        <div class="rounded-xl border border-slate-200 bg-slate-50">
                            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $subjectName }}</p>
                                    <p class="text-xs text-slate-500">{{ $scores->count() }} penilaian</p>
                                </div>
                            </div>
                            <div class="divide-y divide-slate-200">
                                @foreach($scores->sortByDesc(fn($score) => [$score->assessment?->tanggal, $score->assessment?->created_at]) as $score)
                                    <div class="flex items-start justify-between gap-3 px-4 py-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">
                                                {{ strtoupper($score->assessment?->type ?? '-') }} - {{ $score->assessment?->title }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Guru: {{ $score->assessment?->teacher?->name ?? '-' }} |
                                                {{ $score->assessment?->tanggal?->translatedFormat('d F Y') ?? 'Tanggal belum diisi' }}
                                            </p>
                                        </div>
                                        <span class="min-w-[70px] rounded-lg bg-white px-3 py-1 text-center text-sm font-bold text-emerald-700 ring-1 ring-emerald-200">
                                            {{ $score->score ?? '-' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
            Belum ada data siswa yang cocok dengan filter.
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $students->links() }}
</div>

<div class="mt-10 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Tabel Nilai per Mata Pelajaran</h3>
            <p class="text-sm text-slate-500">Berisi daftar nilai berdasarkan mapel dengan filter yang diterapkan.</p>
        </div>
    </div>

    @forelse($scoresBySubject as $subjectName => $scores)
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="text-base font-semibold text-slate-900">{{ $subjectName }}</p>
                    <p class="text-xs text-slate-500">{{ $scores->count() }} entri nilai</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Penilaian</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3 text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($scores as $score)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $score->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $score->student?->classroom?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $score->assessment?->title ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">Kelas {{ $score->assessment?->classroom?->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 uppercase">{{ $score->assessment?->type ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $score->assessment?->tanggal?->translatedFormat('d F Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $score->assessment?->teacher?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">{{ $score->score ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
            Belum ada nilai yang cocok dengan filter.
        </div>
    @endforelse
</div>
@endsection
