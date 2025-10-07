@extends('layouts.app')

@section('title', 'Laporan Bulanan | ' . config('app.name'))
@section('page-title', 'Laporan Bulanan')
@section('page-subtitle', 'Tinjau ringkasan absensi dan jurnal mengajar untuk periode tertentu.')

@section('content')
@php
    use Illuminate\Support\Carbon;

    $months = collect(range(1, 12))->mapWithKeys(fn (int $month) => [
        $month => Carbon::create(null, $month, 1)->translatedFormat('F'),
    ]);

    $totalDurationMinutes = $report['journals']['summary']['total_durasi_menit'];
    $durationLabel = sprintf('%02dj %02dm', intdiv($totalDurationMinutes, 60), $totalDurationMinutes % 60);
@endphp

<form method="GET" action="{{ route('admin.reports.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label for="month" class="block text-xs font-semibold text-slate-500 uppercase">Bulan</label>
        <select name="month" id="month"
                class="mt-1 w-40 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            @foreach($months as $value => $label)
                <option value="{{ $value }}" @selected($selectedMonth === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="year" class="block text-xs font-semibold text-slate-500 uppercase">Tahun</label>
        <input type="number" name="year" id="year" value="{{ $selectedYear }}" min="2000" max="2100"
               class="mt-1 w-32 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>
    <div>
        <label for="classroom_id" class="block text-xs font-semibold text-slate-500 uppercase">Kelas</label>
        <select name="classroom_id" id="classroom_id"
                class="mt-1 w-48 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Semua Kelas</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected($selectedClassroomId === $classroom->id)>{{ $classroom->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="student" class="block text-xs font-semibold text-slate-500 uppercase">Cari Siswa</label>
        <input type="text" name="student" id="student" value="{{ $selectedStudentKeyword }}" placeholder="Nama atau NIS"
               class="mt-1 w-56 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>
    <div>
        <label for="date" class="block text-xs font-semibold text-slate-500 uppercase">Tanggal Spesifik</label>
        <input type="date" name="date" id="date" value="{{ $selectedSpecificDate }}"
               class="mt-1 w-48 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
    </div>
    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Tampilkan</button>

    @php
        $downloadParams = ['month' => $selectedMonth, 'year' => $selectedYear];
        if ($selectedClassroomId) {
            $downloadParams['classroom_id'] = $selectedClassroomId;
        }
        if ($selectedStudentKeyword) {
            $downloadParams['student'] = $selectedStudentKeyword;
        }
        if ($selectedSpecificDate) {
            $downloadParams['date'] = $selectedSpecificDate;
        }
    @endphp

    <div class="flex items-center gap-2 text-xs text-slate-500">
        <span>Unduh:</span>
        <a href="{{ route('admin.reports.export-pdf', $downloadParams) }}" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-600 hover:bg-slate-100">PDF</a>
        <a href="{{ route('admin.reports.export-excel', $downloadParams) }}" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-600 hover:bg-slate-100">Excel</a>
    </div>
</form>

<section class="mb-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
    <h3 class="text-base font-semibold text-slate-900">Ringkasan Absensi</h3>
    <p class="mt-1 text-sm text-slate-500">Periode: {{ $report['period']['label'] }}</p>
    @if($selectedClassroomName)
        <p class="text-xs text-emerald-700 mt-1">Menampilkan data untuk kelas <span class="font-semibold">{{ $selectedClassroomName }}</span>.</p>
    @endif
    @if($selectedStudentKeyword)
        <p class="text-xs text-emerald-700 mt-1">Filter siswa: <span class="font-semibold">{{ $selectedStudentKeyword }}</span>.</p>
    @endif
    @if($selectedSpecificDate)
        <p class="text-xs text-emerald-700 mt-1">Hanya menampilkan data pada tanggal <span class="font-semibold">{{ Carbon::parse($selectedSpecificDate)->translatedFormat('d F Y') }}</span>.</p>
    @endif

    <div class="mt-4 grid gap-4 md:grid-cols-5">
        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <p class="text-xs font-semibold uppercase text-green-700">Hadir</p>
            <p class="mt-2 text-2xl font-bold text-green-800">{{ $report['attendance']['summary']['hadir'] }}</p>
        </div>
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
            <p class="text-xs font-semibold uppercase text-yellow-700">Izin</p>
            <p class="mt-2 text-2xl font-bold text-yellow-800">{{ $report['attendance']['summary']['izin'] }}</p>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
            <p class="text-xs font-semibold uppercase text-purple-700">Sakit</p>
            <p class="mt-2 text-2xl font-bold text-purple-800">{{ $report['attendance']['summary']['sakit'] }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-semibold uppercase text-red-700">Alpa</p>
            <p class="mt-2 text-2xl font-bold text-red-800">{{ $report['attendance']['summary']['alpa'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Total Rangkuman</p>
            <p class="mt-2 text-lg font-semibold text-slate-800">{{ $report['attendance']['summary']['total_records'] }} catatan</p>
            <p class="text-xs text-slate-500">{{ $report['attendance']['summary']['total_students'] }} siswa</p>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-white">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Siswa</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3 text-center">Hadir</th>
                    <th class="px-4 py-3 text-center">Izin</th>
                    <th class="px-4 py-3 text-center">Sakit</th>
                    <th class="px-4 py-3 text-center">Alpa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
            @forelse($report['attendance']['by_students'] as $entry)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $entry['student']['name'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $entry['student']['classroom'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-green-700 font-semibold">{{ $entry['totals']['hadir'] }}</td>
                    <td class="px-4 py-3 text-center text-yellow-700 font-semibold">{{ $entry['totals']['izin'] }}</td>
                    <td class="px-4 py-3 text-center text-purple-700 font-semibold">{{ $entry['totals']['sakit'] }}</td>
                    <td class="px-4 py-3 text-center text-red-700 font-semibold">{{ $entry['totals']['alpa'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data absensi pada periode ini.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
    <h3 class="text-base font-semibold text-slate-900">Laporan Jurnal Mengajar</h3>
    <p class="mt-1 text-sm text-slate-500">Analisis lengkap tersedia di submenu Laporan Jurnal Mengajar.</p>
    <a href="{{ route('admin.teacher-reports.index') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">Buka Laporan Jurnal Mengajar</a>
</section>
</div>
@endsection
