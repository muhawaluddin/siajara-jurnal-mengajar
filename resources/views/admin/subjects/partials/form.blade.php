@php($subject = $subject ?? null)

<div class="space-y-5">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-600">Nama Mata Pelajaran</label>
        <input type="text" name="name" id="name" value="{{ old('name', $subject->name ?? '') }}" required maxlength="150"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.subjects.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>
