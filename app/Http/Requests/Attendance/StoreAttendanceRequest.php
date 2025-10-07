<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    /** Izinkan semua request yang terautentikasi. */
    public function authorize(): bool
    {
        return true;
    }

    /** Aturan validasi untuk input absensi. */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'date' => [
                'required',
                'date',
                Rule::unique('attendances')->where(fn ($query) =>
                    $query->where('student_id', $this->input('student_id'))
                ),
            ],
            'status' => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
        ];
    }
}
