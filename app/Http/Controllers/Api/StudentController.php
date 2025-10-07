<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /** Tampilkan daftar siswa dengan pagination. */
    public function index(Request $request)
    {
        $students = Student::query()
            ->when($request->filled('class'), fn ($query) => $query->where('class', $request->string('class')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return StudentResource::collection($students);
    }

    /** Simpan data siswa baru. */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = Student::query()->create($request->validated());

        return StudentResource::make($student)->response()->setStatusCode(201);
    }

    /** Tampilkan detail siswa tertentu. */
    public function show(Student $student): StudentResource
    {
        return StudentResource::make($student);
    }

    /** Perbarui data siswa. */
    public function update(UpdateStudentRequest $request, Student $student): StudentResource
    {
        $student->update($request->validated());

        return StudentResource::make($student);
    }

    /** Hapus siswa beserta relasi absensi. */
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json(null, 204);
    }
}
