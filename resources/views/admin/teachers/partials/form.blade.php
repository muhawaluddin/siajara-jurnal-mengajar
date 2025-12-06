@php($teacher = $teacher ?? null)
@php($selectedSubjects = old('subject_ids', $teacher?->subjects?->pluck('id')->all() ?? []))
@php($subjects = $subjects ?? collect())

<div class="space-y-5">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-slate-600">Nama Guru</label>
            <input type="text" name="name" id="name" value="{{ old('name', $teacher->name ?? '') }}" required maxlength="255"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-slate-600">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $teacher->email ?? '') }}" required maxlength="255"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-600">{{ isset($teacher) ? 'Password Baru (opsional)' : 'Password' }}</label>
        <input type="password" name="password" id="password" @if(!isset($teacher)) required @endif minlength="8"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Mapel yang Diampu</h3>
            <p class="text-xs text-slate-500">Pilih mapel agar guru bisa menginput nilai.</p>
        </div>
        @if($subjects->count())
            <div class="mt-3 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @foreach($subjects as $subject)
                    <label class="flex items-center gap-3 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-slate-200">
                        <input type="checkbox"
                               name="subject_ids[]"
                               value="{{ $subject->id }}"
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                               @checked(in_array($subject->id, $selectedSubjects, true)) />
                        <span class="text-sm font-medium text-slate-700">{{ $subject->name }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <p class="mt-2 text-sm text-slate-500">Belum ada data mapel. Tambahkan mapel terlebih dahulu.</p>
        @endif
        @error('subject_ids')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('subject_ids.*')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.teachers.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>
