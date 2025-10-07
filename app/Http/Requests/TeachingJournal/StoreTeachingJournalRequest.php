<?php

namespace App\Http\Requests\TeachingJournal;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingJournalRequest extends FormRequest
{
    /** Izinkan semua request yang terautentikasi. */
    public function authorize(): bool
    {
        return true;
    }

    /** Aturan validasi untuk jurnal mengajar baru. */
    public function rules(): array
    {
        return [
            'guru_id' => ['required', 'exists:users,id'],
            'mata_pelajaran' => ['required', 'string', 'max:150'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
