@php($teacher = $teacher ?? null)

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

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.teachers.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>
