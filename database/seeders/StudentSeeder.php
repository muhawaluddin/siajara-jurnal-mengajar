<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Seed contoh data siswa.
     */
    public function run(): void
    {
        $students = [
            ['name' => 'Aisyah Rahma', 'class' => 'X IPA 1', 'nis' => '22001'],
            ['name' => 'Budi Santoso', 'class' => 'X IPA 1', 'nis' => '22002'],
            ['name' => 'Citra Lestari', 'class' => 'X IPS 2', 'nis' => '22003'],
            ['name' => 'Dimas Mahendra', 'class' => 'XI IPA 3', 'nis' => '22004'],
            ['name' => 'Eka Pratama', 'class' => 'XI IPS 1', 'nis' => '22005'],
        ];

        foreach ($students as $student) {
            $classroom = Classroom::query()->firstOrCreate(['name' => $student['class']]);

            Student::query()->updateOrCreate(
                ['nis' => $student['nis']],
                ['name' => $student['name'], 'classroom_id' => $classroom->id, 'nis' => $student['nis']]
            );
        }
    }
}
