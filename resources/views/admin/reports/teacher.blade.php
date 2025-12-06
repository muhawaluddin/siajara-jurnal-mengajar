@extends('layouts.app')

@section('title', 'Laporan Durasi Mengajar | ' . config('app.name'))

@section('page-title', 'Durasi Mengajar Guru')
@section('page-subtitle', 'Pantau guru dengan jam mengajar tertinggi berdasarkan periode tertentu.')

@section('content')
<form method="GET" action="{{ route('admin.teacher-reports.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div>
        <label for="teacher_id" class="block text-xs font-semibold text-emerald-600 uppercase">Guru</label>
        <select name="teacher_id" id="teacher_id" class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Semua Guru</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected($filters['teacher_id'] === $teacher->id)>{{ $teacher->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="name" class="block text-xs font-semibold text-emerald-600 uppercase">Cari Nama</label>
        <input type="text" name="name" id="name" value="{{ $filters['name'] }}" placeholder="Cari guru..."
               class="mt-1 w-full rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
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
        <a href="{{ route('admin.teacher-reports.index') }}" class="inline-flex items-center rounded-lg border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Reset</a>
        <span class="text-xs text-slate-500">Total durasi: {{ sprintf('%02dj %02dm', intdiv($totalMinutes, 60), $totalMinutes % 60) }}</span>
    </div>
</form>

@php
    $totalMeetings = $summaries->sum('total_pertemuan');
    $teacherCount = $summaries->count();
    $topTeacher = $summaries->sortByDesc('total_durasi_menit')->first();
@endphp

<div class="-mx-2 sm:-mx-4 lg:-mx-6 space-y-6">
<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mx-2 sm:mx-4 lg:mx-6">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Total Pertemuan</p>
        <p class="mt-3 text-3xl font-bold text-emerald-800">{{ $totalMeetings }}</p>
        <p class="mt-1 text-xs text-emerald-600">Akumulasi seluruh guru</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Total Durasi</p>
        <p class="mt-3 text-3xl font-bold text-emerald-800">{{ sprintf('%02dj %02dm', intdiv($totalMinutes, 60), $totalMinutes % 60) }}</p>
        <p class="mt-1 text-xs text-emerald-600">Jam mengajar tercatat</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Jumlah Guru</p>
        <p class="mt-3 text-3xl font-bold text-emerald-800">{{ $teacherCount }}</p>
        <p class="mt-1 text-xs text-emerald-600">Yang memiliki jurnal pada periode ini</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Guru Teraktif</p>
        <p class="mt-3 text-base font-semibold text-emerald-800">{{ $topTeacher['guru']['name'] ?? '–' }}</p>
        <p class="mt-1 text-xs text-emerald-600">Durasi {{ $topTeacher ? sprintf('%02dj %02dm', intdiv($topTeacher['total_durasi_menit'], 60), $topTeacher['total_durasi_menit'] % 60) : '0j 00m' }}</p>
    </div>
</section>
@php
    $preselected = $filters['teacher_id']
        ? $summaries->firstWhere(fn ($item) => ($item['guru']['id'] ?? null) === $filters['teacher_id'])
        : null;
@endphp

<div
    x-data="{
        summaries: {{ \Illuminate\Support\Js::from($summaries) }},
        selectedId: {{ $filters['teacher_id'] ? \Illuminate\Support\Js::from($filters['teacher_id']) : 'null' }},
        detail: {{ $preselected ? \Illuminate\Support\Js::from($preselected) : 'null' }},
        selectedRecord: {{ $preselected ? \Illuminate\Support\Js::from($preselected['records'][0] ?? null) : 'null' }},
        showModal: {{ $preselected ? 'true' : 'false' }},
        select(teacher) {
            this.detail = teacher;
            this.selectedId = teacher?.guru?.id ?? null;
            this.selectedRecord = (teacher?.records ?? [])[0] ?? null;
            this.showModal = true;
        },
        setRecord(record) {
            this.selectedRecord = record;
        },
        formatDuration(total) {
            total = Number(total ?? 0);
            const hours = Math.floor(total / 60);
            const minutes = total % 60;
            return `${String(hours).padStart(2, '0')}j ${String(minutes).padStart(2, '0')}m`;
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }
            const date = new Date(value);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        },
        formatTimeRange(record) {
            if (!record) return '-';
            const start = record.jam_mulai ?? '';
            const end = record.jam_selesai ?? '';
            if (!start && !end) return '-';
            return [start, end].filter(Boolean).join(' - ');
        }
    }"
    @keydown.escape.window="showModal = false"
    class="mt-6 mx-2 sm:mx-4 lg:mx-6"
>
    <div class="rounded-2xl border border-emerald-200 bg-white shadow-sm overflow-hidden">
        <div>
            <table class="min-w-full divide-y divide-emerald-100 text-sm text-slate-700">
                <thead class="bg-emerald-50 text-emerald-800 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3 text-left">Guru</th>
                        <th class="px-4 py-3 text-center">Total Pertemuan</th>
                        <th class="px-4 py-3 text-center">Durasi Mengajar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-100">
                @forelse($summaries as $row)
                    @php
                        $teacherId = $row['guru']['id'] ?? null;
                        $teacherIdJs = $teacherId !== null ? \Illuminate\Support\Js::from($teacherId) : 'null';
                    @endphp
                    <tr class="cursor-pointer transition"
                        @click="select({{ \Illuminate\Support\Js::from($row) }})"
                        @keyup.enter="select({{ \Illuminate\Support\Js::from($row) }})"
                        :class="selectedId === {{ $teacherIdJs }} ? 'bg-emerald-50/80 text-emerald-800' : 'hover:bg-emerald-50'"
                        tabindex="0">
                        <td class="px-4 py-3 font-semibold flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-600"
                                 :class="selectedId === {{ $teacherIdJs }} ? 'rotate-90 transition-transform' : ''"
                                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                            <span>{{ $row['guru']['name'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $row['total_pertemuan'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-emerald-700">
                            {{ sprintf('%02dj %02dm', intdiv($row['total_durasi_menit'], 60), $row['total_durasi_menit'] % 60) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">Belum ada data jurnal untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-xs text-slate-500">Klik nama guru untuk melihat detail pertemuan mengajarnya.</div>

    <div x-show="showModal" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative w-full max-w-3xl max-h-[85vh] overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between border-b border-emerald-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-emerald-800" x-text="detail?.guru?.name ?? 'Guru'">Guru</h3>
                    <p class="text-xs text-slate-500 mt-1">Total pertemuan: <span x-text="detail?.total_pertemuan ?? 0"></span> • Durasi: <span x-text="detail ? formatDuration(detail.total_durasi_menit) : '0j 00m'"></span></p>
                </div>
                <button type="button" class="inline-flex items-center justify-center rounded-full bg-emerald-50 p-2 text-emerald-600 hover:bg-emerald-100" @click="showModal = false">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-5">
                <template x-if="!detail || (detail.records ?? []).length === 0">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-5 text-sm text-slate-600 text-center">
                        Belum ada catatan detail untuk guru ini pada periode yang dipilih.
                    </div>
                </template>

                <template x-if="detail && (detail.records ?? []).length">
                    <div class="space-y-4 lg:grid lg:grid-cols-3 lg:gap-4 lg:space-y-0">
                        <div class="space-y-3 lg:col-span-2">
                            <template x-for="(record, index) in detail.records" :key="`${record.id ?? ''}-${index}`">
                                <button type="button"
                                        class="w-full text-left rounded-xl border px-4 py-3 transition"
                                        @click="setRecord(record)"
                                        :class="selectedRecord?.id === record.id ? 'border-emerald-300 bg-emerald-50 shadow-sm' : 'border-emerald-100 bg-emerald-50/80 hover:border-emerald-200'">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm font-semibold text-emerald-800">
                                        <span x-text="formatDate(record.tanggal)"></span>
                                        <span x-text="`${record.durasi_menit ?? 0} menit`"></span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-700 font-semibold" x-text="record.mata_pelajaran ?? '-' "></p>
                                    <p class="mt-1 inline-flex items-center gap-2 rounded-full bg-white/80 px-2.5 py-1 text-[11px] font-semibold text-emerald-700" x-show="record.classroom?.name">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        <span x-text="record.classroom?.name"></span>
                                    </p>
                                    <template x-if="record.topik">
                                        <p class="text-xs text-slate-500 mt-1">Topik: <span x-text="record.topik"></span></p>
                                    </template>
                                    <template x-if="record.catatan">
                                        <p class="text-xs text-slate-500 mt-1">Catatan: <span x-text="record.catatan"></span></p>
                                    </template>
                                </button>
                            </template>
                        </div>

                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4 lg:col-span-1" x-show="selectedRecord" x-cloak>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Detail Pertemuan</p>
                                    <p class="text-sm font-semibold text-emerald-900 mt-1" x-text="formatDate(selectedRecord?.tanggal)"></p>
                                    <p class="text-xs text-slate-600" x-text="formatTimeRange(selectedRecord)"></p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700" x-text="`${selectedRecord?.durasi_menit ?? 0} menit`"></span>
                            </div>
                            <div class="mt-3 space-y-2 text-sm text-slate-700">
                                <div class="font-semibold" x-text="selectedRecord?.mata_pelajaran ?? '-'"></div>
                                <div class="inline-flex items-center gap-2 rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-emerald-700" x-show="selectedRecord?.classroom?.name">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    <span x-text="selectedRecord?.classroom?.name"></span>
                                </div>
                                <template x-if="selectedRecord?.topik">
                                    <p class="text-xs text-slate-600">Topik: <span class="font-medium" x-text="selectedRecord.topik"></span></p>
                                </template>
                                <template x-if="selectedRecord?.catatan">
                                    <p class="text-xs text-slate-600">Catatan: <span class="font-medium" x-text="selectedRecord.catatan"></span></p>
                                </template>
                            </div>
                            <template x-if="selectedRecord?.documentation_url">
                                <div class="mt-3 space-y-2 rounded-lg border border-emerald-100 bg-white p-2">
                                    <img :src="selectedRecord.documentation_url" alt="Dokumentasi kelas" class="aspect-video w-full rounded-lg object-cover">
                                    <a :href="selectedRecord.documentation_url" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0 6-6m-6 6-6-6"/></svg>
                                        Buka dokumentasi
                                    </a>
                                </div>
                            </template>
                            <template x-if="!selectedRecord">
                                <p class="text-xs text-slate-500">Pilih salah satu pertemuan untuk melihat detailnya.</p>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
