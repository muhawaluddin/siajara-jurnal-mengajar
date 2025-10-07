<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    /** Tampilkan daftar absensi dengan filter opsional. */
    public function index(Request $request)
    {
        $attendances = Attendance::query()
            ->with('student')
            ->when($request->filled('student_id'), fn ($query) =>
                $query->where('student_id', $request->integer('student_id'))
            )
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->string('status'))
            )
            ->when($request->filled('month') && $request->filled('year'), function ($query) use ($request) {
                $start = Carbon::create($request->integer('year'), $request->integer('month'), 1)->startOfMonth();
                $end = (clone $start)->endOfMonth();
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            })
            ->orderByDesc('date')
            ->paginate($request->integer('per_page', 15));

        return AttendanceResource::collection($attendances);
    }

    /** Simpan absensi siswa baru. */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $attendance = Attendance::query()->create($request->validated());

        return AttendanceResource::make($attendance->load('student'))
            ->response()
            ->setStatusCode(201);
    }

    /** Tampilkan detail absensi. */
    public function show(Attendance $attendance): AttendanceResource
    {
        return AttendanceResource::make($attendance->load('student'));
    }

    /** Perbarui data absensi. */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): AttendanceResource
    {
        $attendance->update($request->validated());

        return AttendanceResource::make($attendance->load('student'));
    }

    /** Hapus catatan absensi. */
    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();

        return response()->json(null, 204);
    }
}
