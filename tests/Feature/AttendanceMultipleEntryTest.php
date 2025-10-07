<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\TeachingJournal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceMultipleEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_have_multiple_attendance_records_in_single_day(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guru->value]);

        $classroom = Classroom::create(['name' => 'X IPA 1']);
        $student = Student::create([
            'name' => 'Budi',
            'classroom_id' => $classroom->id,
        ]);

        $date = Carbon::parse('2025-10-04');

        $journalMorning = TeachingJournal::create([
            'guru_id' => $user->id,
            'subject_id' => null,
            'mata_pelajaran' => 'Matematika',
            'tanggal' => $date->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
            'topik' => 'Persamaan Linear',
            'catatan' => null,
        ]);

        $journalAfternoon = TeachingJournal::create([
            'guru_id' => $user->id,
            'subject_id' => null,
            'mata_pelajaran' => 'Fisika',
            'tanggal' => $date->toDateString(),
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:30',
            'topik' => 'Gaya',
            'catatan' => null,
        ]);

        $this->actingAs($user)
            ->post(route('web.attendances.bulk'), [
                'class' => $classroom->id,
                'date' => $date->toDateString(),
                'statuses' => [
                    $student->id => 'hadir',
                ],
                'teaching_journal_id' => $journalMorning->id,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('web.attendances.bulk'), [
                'class' => $classroom->id,
                'date' => $date->toDateString(),
                'statuses' => [
                    $student->id => 'sakit',
                ],
                'teaching_journal_id' => $journalAfternoon->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('attendances', 2);

        $storedDate = $date->startOfDay()->toDateTimeString();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'date' => $storedDate,
            'teaching_journal_id' => $journalMorning->id,
            'status' => 'hadir',
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'date' => $storedDate,
            'teaching_journal_id' => $journalAfternoon->id,
            'status' => 'sakit',
        ]);
    }
}
