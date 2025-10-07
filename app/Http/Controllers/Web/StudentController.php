<?php

namespace App\Http\Controllers\Web;

use App\Imports\StudentsImport;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->with('classroom')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhereHas('classroom', fn ($relation) => $relation->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $classrooms = Classroom::orderBy('name')->get();

        return view('students.index', compact('students', 'classrooms'));
    }

    public function create(): View
    {
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.create', compact('classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:students,nis'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
        ]);

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('status', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        $classrooms = Classroom::orderBy('name')->get();

        return view('students.edit', compact('student', 'classrooms'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:students,nis,' . $student->id],
            'classroom_id' => ['required', 'exists:classrooms,id'],
        ]);

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('status', 'Data siswa berhasil diperbarui.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        $import = new StudentsImport();

        try {
            DB::transaction(function () use ($import, $request) {
                Excel::import($import, $request->file('file'));
            });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withErrors([
                    'file' => 'Terjadi kesalahan saat mengimpor data. Pastikan format file sudah benar.',
                ]);
        }

        $summary = $import->summary();

        $response = redirect()->route('admin.students.index')->with('status', sprintf(
            'Import selesai: %d data baru, %d diperbarui, %d dilewati.',
            $summary['created'],
            $summary['updated'],
            $summary['skipped'],
        ));

        if ($errors = $import->errors()) {
            $response->with('import_errors', $errors);
        }

        return $response;
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('status', 'Data siswa berhasil dihapus.');
    }
}
