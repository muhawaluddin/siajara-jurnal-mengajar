<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    /** Izinkan semua request yang terautentikasi. */
    public function authorize(): bool
    {
        return true;
    }

    /** Aturan validasi untuk memperbarui absensi. */
    public function rules(): array
    {
        $attendance = $this->route('attendance');
        $attendanceId = $attendance?->id;
        $studentId = $this->input('student_id', $attendance?->student_id);

        return [
            'student_id' => ['sometimes', 'exists:students,id'],
            'date' => [
                'sometimes',
                'date',
                Rule::unique('attendances')
                    ->ignore($attendanceId)
                    ->where(fn ($query) => $studentId
                        ? $query->where('student_id', $studentId)
                        : $query
                    ),
            ],
            'status' => ['sometimes', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
        ];
    }
}
