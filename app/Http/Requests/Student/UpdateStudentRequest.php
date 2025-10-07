<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /** Izinkan semua request yang terautentikasi. */
    public function authorize(): bool
    {
        return true;
    }

    /** Aturan validasi untuk memperbarui siswa. */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'class' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
