<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TeachingJournalSeeder extends Seeder
{
    /**
     * Seed contoh data jurnal mengajar.
     */
    public function run(): void
    {
        $guru = User::query()->where('role', UserRole::Guru)->first();

        if ($guru === null) {
            $guru = User::factory()->create([
                'name' => 'Guru Utama',
                'email' => 'guru@example.com',
                'role' => UserRole::Guru->value,
            ]);
        }

        $baseDate = Carbon::now()->startOfMonth();
        $subject = Subject::firstOrCreate(['name' => 'Matematika']);

        foreach (range(0, 4) as $week) {
            TeachingJournal::query()->updateOrCreate(
                [
                    'guru_id' => $guru->id,
                    'tanggal' => $baseDate->copy()->addDays($week * 2)->toDateString(),
                    'mata_pelajaran' => $subject->name,
                ],
                [
                    'subject_id' => $subject->id,
                    'jam_mulai' => '08:00',
                    'jam_selesai' => '09:40',
                    'topik' => 'Pembahasan Materi Mingguan ' . ($week + 1),
                    'catatan' => 'Catatan refleksi pembelajaran minggu ke-' . ($week + 1),
                ]
            );
        }
    }
}
