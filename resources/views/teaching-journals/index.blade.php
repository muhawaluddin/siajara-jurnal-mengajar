@extends('layouts.app')

@section('title', 'Jurnal Mengajar | ' . config('app.name'))
@section('page-title', 'Jurnal Mengajar')
@section('page-subtitle', 'Dokumentasikan setiap sesi mengajar yang telah dilakukan.')

@section('content')
@if(session('status'))
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('status') }}
    </div>
@endif

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <form method="GET" action="{{ route('web.teaching-journals.index') }}" class="flex items-center gap-3">
        <select name="month" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Semua Bulan</option>
            @foreach(range(1, 12) as $month)
                <option value="{{ $month }}" @selected((int) request('month') === $month)>{{ \Illuminate\Support\Carbon::createFromDate(null, $month, 1)->translatedFormat('F') }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Filter</button>
        <a href="{{ route('web.teaching-journals.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100">Reset</a>
    </form>

    <a href="{{ route('web.teaching-journals.create') }}"
       class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
        Tambah Jurnal
    </a>
</div>

<div
    x-data="journalViewer()"
    class="relative overflow-x-auto rounded-xl border border-slate-200"
>
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Mata Pelajaran</th>
                <th class="px-4 py-3">Jam</th>
                <th class="px-4 py-3">Topik</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse($journals as $journal)
            @php
                $mataPelajaran = $journal->subject->name ?? $journal->mata_pelajaran;
                $detail = [
                    'id' => $journal->id,
                    'tanggal_label' => $journal->tanggal->translatedFormat('l, d F Y'),
                    'tanggal_raw' => $journal->tanggal->toDateString(),
                    'mata_pelajaran' => $mataPelajaran,
                    'jam_mulai' => $journal->jam_mulai->format('H:i'),
                    'jam_selesai' => $journal->jam_selesai->format('H:i'),
                    'durasi_menit' => $journal->jam_mulai->diffInMinutes($journal->jam_selesai),
                    'topik' => $journal->topik,
                    'catatan' => $journal->catatan,
                    'dibuat' => $journal->created_at?->translatedFormat('d F Y H:i'),
                    'diubah' => $journal->updated_at?->translatedFormat('d F Y H:i'),
                ];
            @endphp
            <tr class="cursor-pointer transition hover:bg-emerald-50/70" @click="viewJournal(@js($detail))">
                <td class="px-4 py-3 text-slate-600">{{ $journal->tanggal->translatedFormat('d F Y') }}</td>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $mataPelajaran }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $journal->jam_mulai->format('H:i') }} - {{ $journal->jam_selesai->format('H:i') }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $journal->topik }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('web.teaching-journals.edit', $journal) }}" class="rounded-lg border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50" @click.stop>Ubah</a>
                        <form method="POST" action="{{ route('web.teaching-journals.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal mengajar ini?');" @click.stop>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada jurnal mengajar.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6" @keydown.escape.window="close()">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="close()"></div>
        <div x-transition.scale class="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-emerald-500/40 bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-white">
                <div>
                    <p class="text-xs uppercase tracking-wide text-emerald-100">Detail Jurnal Mengajar</p>
                    <h3 class="text-xl font-semibold" x-text="journal?.mata_pelajaran ?? ''"></h3>
                    <p class="text-sm text-emerald-100/80" x-text="journal?.tanggal_label ?? ''"></p>
                </div>
                <button @click="close()" class="rounded-full bg-white/25 p-2 text-white transition hover:bg-white/35">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid gap-6 px-6 py-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu Pelaksanaan</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900"><span x-text="journal?.jam_mulai"></span> - <span x-text="journal?.jam_selesai"></span></p>
                        <p class="text-sm text-slate-500">Durasi <span class="font-semibold text-emerald-600" x-text="journal ? journal.durasi_menit + ' menit' : '-' "></span></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Topik Pembelajaran</p>
                        <p class="mt-2 text-base font-semibold text-slate-900" x-text="journal?.topik"></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600" x-text="journal?.catatan || 'Tidak ada catatan tambahan.'"></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500">
                        <p>Dicatat: <span class="font-medium" x-text="journal?.dibuat || '-' "></span></p>
                        <p>Diubah terakhir: <span class="font-medium" x-text="journal?.diubah || '-' "></span></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <a :href="journal ? '{{ url('teaching-journals') }}/' + journal.id + '/edit' : '#'" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.25 2.25 0 1 1 3.182 3.182L9 18.75 4.5 19.5l.75-4.5 11.612-10.513Z"/></svg>
                    Ubah Jurnal
                </a>
                <button @click="close()" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    {{ $journals->links() }}
</div>

<script>
    function journalViewer() {
        return {
            open: false,
            journal: null,
            viewJournal(data) {
                this.journal = data;
                this.open = true;
                document.documentElement.classList.add('overflow-hidden');
            },
            close() {
                this.open = false;
                this.journal = null;
                document.documentElement.classList.remove('overflow-hidden');
            },
        };
    }
</script>
@endsection
