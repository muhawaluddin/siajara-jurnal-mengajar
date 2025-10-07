@extends('layouts.app')

@section('title', 'Data Siswa | ' . config('app.name'))
@section('page-title', 'Data Siswa')
@section('page-subtitle', 'Kelola data siswa dan kelas yang diajar.')

@section('content')
@if(session('status'))
    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('status') }}
    </div>
@endif

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <form method="GET" action="{{ route('admin.students.index') }}" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, atau kelas"
               class="w-64 rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Cari</button>
    </form>

    <div class="flex flex-col items-stretch gap-2 md:flex-row md:items-center">
        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
            @csrf
            <input type="file" name="file" accept=".xlsx,.csv" class="text-xs text-slate-600 focus:outline-none focus:ring-0" required />
            <button type="submit" class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Import</button>
        </form>

        <a href="{{ route('admin.students.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
            Tambah Siswa
        </a>
    </div>
</div>

@error('file')
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-600">
        {{ $message }}
    </div>
@enderror

@if(session('import_errors'))
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
        <p class="font-semibold">Beberapa baris dilewati saat import:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach(session('import_errors') as $importError)
                <li>{{ $importError }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="overflow-hidden rounded-xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">NIS</th>
                <th class="px-4 py-3">Kelas</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
        @forelse($students as $student)
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800">{{ $student->name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $student->nis ?? '-' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $student->classroom?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.students.edit', $student) }}" class="rounded-lg border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Ubah</a>
                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus data siswa ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data siswa.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $students->links() }}
</div>
@endsection
