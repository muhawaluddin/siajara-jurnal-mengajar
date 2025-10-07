<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\TeachingJournal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Seed contoh data absensi bulanan.
     */
    public function run(): void
    {
        $students = Student::with('classroom')->get();
        $journalsGrouped = TeachingJournal::all()->groupBy(fn ($journal) => $journal->tanggal->toDateString());
        $start = Carbon::now()->startOfMonth();

        foreach ($students as $student) {
            foreach (range(0, 9) as $offset) {
                $date = $start->copy()->addDays($offset);

                $journal = optional($journalsGrouped->get($date->toDateString()))?->random();

                Attendance::query()->updateOrCreate(
                    ['student_id' => $student->id, 'date' => $date->toDateString()],
                    [
                        'status' => Arr::random(['hadir', 'izin', 'sakit', 'alpa']),
                        'teaching_journal_id' => $journal?->id,
                    ]
                );
            }
        }
    }
}
