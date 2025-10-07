@php($teachingJournal = $teachingJournal ?? null)

<div class="space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="subject_id" class="block text-sm font-medium text-slate-600">Mata Pelajaran</label>
            <select name="subject_id" id="subject_id" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Pilih mata pelajaran</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((int) old('subject_id', $teachingJournal->subject_id ?? 0) === $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @if($subjects->isEmpty())
                <p class="mt-2 text-sm text-amber-600">Belum ada data mata pelajaran. Silakan tambahkan melalui menu Master Mapel.</p>
            @endif
            @error('subject_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="tanggal" class="block text-sm font-medium text-slate-600">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', optional($teachingJournal?->tanggal)->format('Y-m-d')) }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('tanggal')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="jam_mulai" class="block text-sm font-medium text-slate-600">Jam Mulai</label>
            <input type="time" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai', optional($teachingJournal?->jam_mulai)->format('H:i')) }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('jam_mulai')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="jam_selesai" class="block text-sm font-medium text-slate-600">Jam Selesai</label>
            <input type="time" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai', optional($teachingJournal?->jam_selesai)->format('H:i')) }}" required
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('jam_selesai')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="topik" class="block text-sm font-medium text-slate-600">Topik Pembelajaran</label>
        <input type="text" name="topik" id="topik" value="{{ old('topik', $teachingJournal->topik ?? '') }}"
               required maxlength="255"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('topik')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="catatan" class="block text-sm font-medium text-slate-600">Catatan</label>
        <textarea name="catatan" id="catatan" rows="4"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200">{{ old('catatan', $teachingJournal->catatan ?? '') }}</textarea>
        @error('catatan')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('web.teaching-journals.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>
