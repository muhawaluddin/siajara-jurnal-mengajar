@php($assessment = $assessment ?? null)
@php($selectedClassroom = old('classroom_id', $assessment->classroom_id ?? ''))
@php($studentsByClassroom = $students->groupBy('classroom_id'))
@php($selectedType = old('type', $assessment->type ?? 'pertemuan'))

<div
    x-data="{
        selectedClassroom: @js((string) $selectedClassroom),
        type: @js($selectedType),
    }"
    class="space-y-6"
>
    @if($subjects->isEmpty())
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Tidak ada mapel yang diampu. Tambahkan mapel pada data guru terlebih dahulu agar dapat menginput nilai.
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="subject_id" class="block text-sm font-medium text-slate-600">Mata Pelajaran</label>
            <select name="subject_id" id="subject_id" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Pilih mapel</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((int) old('subject_id', $assessment->subject_id ?? 0) === $subject->id)>{{ $subject->name }}</option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="classroom_id" class="block text-sm font-medium text-slate-600">Kelas</label>
            <select name="classroom_id" id="classroom_id" required x-model="selectedClassroom"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                <option value="">Pilih kelas</option>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" @selected((string) old('classroom_id', $assessment->classroom_id ?? '') === (string) $classroom->id)>{{ $classroom->name }}</option>
                @endforeach
            </select>
            @error('classroom_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="title" class="block text-sm font-medium text-slate-600">Judul Penilaian</label>
            <input type="text" name="title" id="title" required maxlength="255"
                   value="{{ old('title', $assessment->title ?? '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('title')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label for="type" class="block text-sm font-medium text-slate-600">Tipe</label>
                <select name="type" id="type" x-model="type" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                    <option value="pertemuan">Pertemuan</option>
                    <option value="uts">UTS</option>
                    <option value="uas">UAS</option>
                </select>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div x-show="type === 'pertemuan'" x-cloak>
                <label for="pertemuan_ke" class="block text-sm font-medium text-slate-600">Pertemuan ke</label>
                <input type="number" name="pertemuan_ke" id="pertemuan_ke" min="1" max="50"
                       value="{{ old('pertemuan_ke', $assessment->pertemuan_ke ?? '') }}"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
                @error('pertemuan_ke')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="tanggal" class="block text-sm font-medium text-slate-600">Tanggal Penilaian</label>
            <input type="date" name="tanggal" id="tanggal"
                   value="{{ old('tanggal', $assessment?->tanggal?->toDateString()) }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
            @error('tanggal')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-700">Nilai Siswa</h3>
                <p class="text-xs text-slate-500">Pilih kelas untuk menampilkan daftar siswa dan input nilai.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700">0 - 100</span>
        </div>

        <div x-show="selectedClassroom === ''" class="mt-4 rounded-lg border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-500">
            Pilih kelas terlebih dahulu untuk menginput nilai siswa.
        </div>

        @foreach($studentsByClassroom as $classroomId => $classStudents)
            <div x-show="selectedClassroom == '{{ $classroomId }}'" x-cloak class="mt-4 rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-700">Kelas {{ $classrooms->firstWhere('id', $classroomId)?->name }}</p>
                    <p class="text-xs text-slate-500">{{ $classStudents->count() }} siswa</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Siswa</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nilai</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($classStudents as $student)
                            @php($scoreValue = old('scores.'.$student->id, optional($assessment?->scores->firstWhere('student_id', $student->id))->score))
                            <tr class="bg-white">
                                <td class="px-4 py-2 text-slate-800 font-semibold">{{ $student->name }}</td>
                                <td class="px-4 py-2">
                                    <input type="number"
                                           name="scores[{{ $student->id }}]"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           value="{{ $scoreValue }}"
                                           :disabled="selectedClassroom != '{{ $classroomId }}'"
                                           class="block w-32 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('web.grades.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
        <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-500">Simpan</button>
    </div>
</div>
