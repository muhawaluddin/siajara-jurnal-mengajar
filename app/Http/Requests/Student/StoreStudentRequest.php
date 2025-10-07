<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /** Izinkan semua request yang terautentikasi. */
    public function authorize(): bool
    {
        return true;
    }

    /** Aturan validasi untuk membuat siswa. */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'class' => ['required', 'string', 'max:100'],
        ];
    }
}
