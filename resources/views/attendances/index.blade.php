@extends('layouts.app')

@section('title', 'Catat Absensi | ' . config('app.name'))
@section('page-title', 'Catat Absensi Harian')
@section('page-subtitle', 'Pilih kelas, tetapkan tanggal, dan tandai kehadiran siswa dalam satu layar.')

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

@if($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <form method="GET" action="{{ route('web.attendances.index') }}" class="flex flex-wrap items-end gap-3">
        <div>
            <label for="class" class="block text-xs font-semibold text-slate-500 uppercase">Kelas</label>
            <select name="class" id="class"
                    class="mt-1 w-48 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                @forelse($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected($selectedClassroomId === $classroom->id)>{{ $classroom->name }}</option>
                @empty
                    <option value="">Belum ada data kelas</option>
                @endforelse
            </select>
        </div>
        <div>
            <label for="date" class="block text-xs font-semibold text-slate-500 uppercase">Tanggal</label>
            <input type="date" name="date" id="date" value="{{ $date->format('Y-m-d') }}"
                   class="mt-1 w-48 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        </div>
        <input type="hidden" name="teaching_journal_id" value="{{ $selectedJournalId }}" />
        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Tampilkan</button>
        <a href="{{ route('web.attendances.history') }}" class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50">Lihat Riwayat</a>
    </form>

    <p class="text-xs text-slate-500">Perubahan otomatis tersimpan saat Anda menekan tombol simpan.</p>
</div>

@if($classrooms->isEmpty())
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-6 text-sm text-amber-700">
        Tambahkan data siswa terlebih dahulu melalui menu <strong>Siswa</strong> sebelum mencatat absensi.
    </div>
@elseif($students->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-600">
        Tidak ditemukan siswa untuk kelas <strong>{{ optional($selectedClassroom)->name ?? 'yang dipilih' }}</strong>. Silakan tambahkan siswa ke kelas ini.
    </div>
@else
    <form method="POST" action="{{ route('web.attendances.bulk') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="class" value="{{ $selectedClassroomId }}">
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <label for="teaching_journal_id" class="block text-xs font-semibold uppercase text-slate-500">Jurnal Mengajar</label>
            <select name="teaching_journal_id" id="teaching_journal_id"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">(Opsional) Pilih jurnal terkait</option>
                @forelse($availableJournals as $journal)
                    @php
                        $subjectName = $journal->subject->name ?? $journal->mata_pelajaran;
                        $label = $journal->tanggal->translatedFormat('d F Y') . ' • ' . $subjectName . ' (' . $journal->jam_mulai->format('H:i') . '-' . $journal->jam_selesai->format('H:i') . ')';
                    @endphp
                    <option value="{{ $journal->id }}" @selected($selectedJournalId === $journal->id)>{{ $label }}</option>
                @empty
                    <option value="" disabled>Belum ada jurnal untuk tanggal ini.</option>
                @endforelse
            </select>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nama Siswa</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Status Kehadiran</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($students as $student)
                    @php
                        $currentStatus = old('statuses.'.$student->id, optional($attendanceMap->get($student->id))->status ?? 'hadir');
                        $selectClasses = $statusStyles[$currentStatus] ?? 'border border-slate-300 bg-white text-slate-700';
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $student->classroom?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <select name="statuses[{{ $student->id }}]"
                                    class="w-full rounded-lg px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition {{ $selectClasses }}">
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" @selected($currentStatus === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3">
            <span class="text-xs text-slate-500">Tanggal absensi: {{ $date->translatedFormat('d F Y') }}</span>
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Simpan Absensi</button>
        </div>
    </form>
    <div class="mt-6 flex flex-wrap items-center gap-3 text-xs text-slate-500">
        <span class="font-semibold">Legenda:</span>
        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-green-700 border border-green-200">Hadir</span>
        <span class="inline-flex items-center rounded-full bg-yellow-50 px-3 py-1 text-yellow-700 border border-yellow-200">Izin</span>
        <span class="inline-flex items-center rounded-full bg-purple-50 px-3 py-1 text-purple-700 border border-purple-200">Sakit</span>
        <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-red-700 border border-red-200">Alpa</span>
    </div>
@endif
@endsection
