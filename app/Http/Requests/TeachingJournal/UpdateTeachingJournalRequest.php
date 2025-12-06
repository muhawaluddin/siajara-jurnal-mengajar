<?php

namespace App\Http\Requests\TeachingJournal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeachingJournalRequest extends FormRequest
{
    /** Izinkan semua request yang terautentikasi. */
    public function authorize(): bool
    {
        return true;
    }

    /** Aturan validasi untuk pembaruan jurnal. */
    public function rules(): array
    {
        return [
            'guru_id' => ['sometimes', 'exists:users,id'],
            'classroom_id' => ['sometimes', 'exists:classrooms,id'],
            'mata_pelajaran' => ['sometimes', 'string', 'max:150'],
            'tanggal' => ['sometimes', 'date'],
            'jam_mulai' => ['sometimes', 'date_format:H:i'],
            'jam_selesai' => ['sometimes', 'date_format:H:i', 'after:jam_mulai'],
            'topik' => ['sometimes', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'documentation' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
