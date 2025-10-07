<?php

namespace Tests\Feature;

use App\Imports\StudentsImport;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_import_creates_updates_and_skips_rows(): void
    {
        $classroom = Classroom::create(['name' => 'X IPA 1']);

        Student::create([
            'name' => 'Alice',
            'nis' => '12345',
            'classroom_id' => $classroom->id,
        ]);

        $import = new StudentsImport();

        $rows = collect([
            collect(['nama' => 'Alice', 'nis' => '12345', 'kelas' => 'X IPA 2']),
            collect(['nama' => 'Charlie', 'nis' => '', 'kelas' => 'XI IPA 1']),
            collect(['nama' => '', 'nis' => '12348', 'kelas' => 'XII IPA 3']),
            collect(['nama' => 'Dina', 'nis' => '12347', 'kelas' => null]),
        ]);

        $import->collection($rows);

        $summary = $import->summary();

        $this->assertSame(2, $summary['created']);
        $this->assertSame(1, $summary['updated']);
        $this->assertSame(1, $summary['skipped']);

        $this->assertDatabaseHas('classrooms', ['name' => 'X IPA 2']);
        $this->assertDatabaseHas('classrooms', ['name' => 'XI IPA 1']);
        $this->assertDatabaseHas('students', ['name' => 'Charlie']);
        $this->assertDatabaseHas('students', ['nis' => '12347']);

        $updatedStudent = Student::where('nis', '12345')->first();
        $this->assertNotNull($updatedStudent);
        $this->assertSame('X IPA 2', $updatedStudent->classroom->name);

        $charlie = Student::where('name', 'Charlie')->first();
        $this->assertNotNull($charlie);
        $this->assertNull($charlie->nis);
        $this->assertSame('XI IPA 1', $charlie->classroom->name);

        $dina = Student::where('nis', '12347')->first();
        $this->assertNotNull($dina);
        $this->assertNull($dina->classroom_id);

        $errors = $import->errors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Baris 4', $errors[0]);
    }

    public function test_students_import_reads_xlsx_file(): void
    {
        $import = new StudentsImport();

        Excel::import($import, base_path('tests/Book2.xlsx'));

        $summary = $import->summary();

        $this->assertSame(2, $summary['created']);
        $this->assertSame(0, $summary['updated']);
        $this->assertSame(0, $summary['skipped']);

        $this->assertDatabaseHas('students', ['name' => 'Muhammad', 'nis' => '9373773']);
        $this->assertDatabaseHas('students', ['name' => 'AKhyar', 'nis' => '3938337']);
        $this->assertDatabaseHas('classrooms', ['name' => 'SMP IX']);
    }
}
