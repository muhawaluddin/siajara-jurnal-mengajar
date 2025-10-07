@php($student = $student ?? null)

<div class="space-y-5">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-600">Nama Siswa</label>
        <input type="text" name="name" id="name" value="{{ old('name', $student->name ?? '') }}"
               required maxlength="255"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nis" class="block text-sm font-medium text-slate-600">NIS</label>
        <input type="text" name="nis" id="nis" value="{{ old('nis', $student->nis ?? '') }}" maxlength="50"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('nis')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="classroom_id" class="block text-sm font-medium text-slate-600">Kelas</label>
        <select name="classroom_id" id="classroom_id" required
                class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
            <option value="">Pilih kelas</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected((int) old('classroom_id', $student->classroom_id ?? 0) === $classroom->id)>
                    {{ $classroom->name }}
                </option>
            @endforeach
        </select>
        @if($classrooms->isEmpty())
            <p class="mt-2 text-sm text-amber-600">Belum ada data kelas. Tambahkan kelas melalui menu Master Kelas.</p>
        @endif
        @error('classroom_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.students.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>
