@extends('layouts.app')

@section('title', 'Nilai Mata Pelajaran | ' . config('app.name'))
@section('page-title', 'Nilai Mata Pelajaran')
@section('page-subtitle', 'Input nilai per pertemuan, UTS, dan UAS untuk kelas yang diampu.')

@section('content')
@if(session('status'))
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('status') }}
    </div>
@endif

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <form method="GET" action="{{ route('web.grades.index') }}" class="flex flex-wrap items-center gap-3">
        <select name="subject_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Semua Mapel</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) request('subject_id') === $subject->id)>{{ $subject->name }}</option>
            @endforeach
        </select>
        <select name="classroom_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Semua Kelas</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected((int) request('classroom_id') === $classroom->id)>{{ $classroom->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Filter</button>
        <a href="{{ route('web.grades.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100">Reset</a>
    </form>

    <a href="{{ route('web.grades.create') }}"
       class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
        Tambah Penilaian
    </a>
</div>

<div class="relative overflow-x-auto rounded-xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-4 py-3">Judul &amp; Tipe</th>
                <th class="px-4 py-3">Mapel &amp; Kelas</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3 text-center">Jumlah Nilai</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse($assessments as $assessment)
            <tr>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($assessment->type) }}</span>
                        @if($assessment->pertemuan_ke)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">Pertemuan {{ $assessment->pertemuan_ke }}</span>
                        @endif
                    </div>
                    <p class="mt-1 font-semibold text-slate-900">{{ $assessment->title }}</p>
                </td>
                <td class="px-4 py-3">
                    <p class="font-semibold text-slate-900">{{ $assessment->subject?->name }}</p>
                    <p class="text-sm text-slate-500">Kelas {{ $assessment->classroom?->name }}</p>
                </td>
                <td class="px-4 py-3 text-slate-600">
                    {{ $assessment->tanggal?->translatedFormat('d F Y') ?? 'Belum diisi' }}
                </td>
                <td class="px-4 py-3 text-center text-slate-800 font-semibold">
                    {{ $assessment->scores_count }}
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('web.grades.edit', $assessment) }}" class="rounded-lg border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Ubah</a>
                        <form method="POST" action="{{ route('web.grades.destroy', $assessment) }}" onsubmit="return confirm('Hapus penilaian ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada penilaian yang dicatat.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $assessments->links() }}
</div>
@endsection
