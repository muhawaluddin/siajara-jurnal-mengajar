<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $name = $this->extractValue($row, ['name', 'nama']);
            $nis = $this->extractValue($row, ['nis', 'nim']);
            $classroomName = $this->extractValue($row, ['classroom', 'kelas', 'class']);

            if (! $name) {
                $this->skipRow($index, 'Kolom nama wajib diisi.');

                continue;
            }

            $classroomId = null;

            if ($classroomName) {
                $classroom = Classroom::firstOrCreate(['name' => $classroomName]);
                $classroomId = $classroom->id;
            }

            $student = null;

            if ($nis) {
                $student = Student::where('nis', $nis)->first();
            }

            if (! $student) {
                $student = Student::where('name', $name)
                    ->when($classroomId, fn ($query) => $query->where('classroom_id', $classroomId))
                    ->first();
            }

            $attributes = [
                'name' => $name,
                'nis' => $nis,
                'classroom_id' => $classroomId,
            ];

            if ($student) {
                $student->fill($attributes);

                if ($student->isDirty()) {
                    $student->save();
                    $this->updated++;
                } else {
                    $this->skipped++;
                }

                continue;
            }

            Student::create($attributes);
            $this->created++;
        }
    }

    private function extractValue(Collection $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! $row->has($key)) {
                continue;
            }

            $value = $row->get($key);

            if (is_string($value)) {
                $value = trim($value);
            } elseif (is_numeric($value)) {
                $value = $this->formatNumericValue($value);
            } elseif ($value !== null) {
                $value = trim((string) $value);
            }

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function formatNumericValue(float|int $value): string
    {
        if (is_float($value)) {
            if (floor($value) === $value) {
                return number_format($value, 0, '', '');
            }

            return rtrim(rtrim(sprintf('%.10f', $value), '0'), '.');
        }

        return (string) $value;
    }

    private function isRowEmpty(Collection $row): bool
    {
        foreach ($row as $value) {
            if (is_string($value) && trim($value) !== '') {
                return false;
            }

            if (is_numeric($value)) {
                return false;
            }

            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function skipRow(int $rowIndex, string $message): void
    {
        $this->skipped++;
        $this->errors[] = sprintf('Baris %d: %s', $this->rowNumber($rowIndex), $message);
    }

    private function rowNumber(int $rowIndex): int
    {
        return $rowIndex + 2; // Tambahan 1 baris untuk heading.
    }

    /** @return array{created:int,updated:int,skipped:int} */
    public function summary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }

    /** @return array<int, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
