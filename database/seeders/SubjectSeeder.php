<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Ilmu Pengetahuan Alam',
            'Ilmu Pengetahuan Sosial',
        ];

        foreach ($subjects as $name) {
            Subject::query()->firstOrCreate(['name' => $name]);
        }
    }
}
