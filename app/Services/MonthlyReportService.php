<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\TeachingJournal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyReportService
{
    /**
     * Susun data laporan bulanan absensi dan jurnal.
     */
    public function generate(int $year, int $month, ?int $classroomId = null, ?string $studentKeyword = null, ?string $specificDate = null): array
    {
        $period = $this->resolvePeriod($year, $month);

        $attendanceCollection = Attendance::query()
            ->with(['student.classroom'])
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->when($classroomId, fn ($query) => $query->whereHas('student', fn ($subQuery) => $subQuery->where('classroom_id', $classroomId)))
            ->when($studentKeyword, function ($query) use ($studentKeyword) {
                $keyword = trim($studentKeyword);

                if ($keyword === '') {
                    return $query;
                }

                $query->whereHas('student', function ($studentQuery) use ($keyword) {
                    $studentQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('nis', 'like', "%{$keyword}%");
                });
            })
            ->when($specificDate, function ($query) use ($specificDate) {
                try {
                    $date = Carbon::parse($specificDate)->toDateString();
                    $query->whereDate('date', $date);
                } catch (\Throwable $th) {
                    // Abaikan jika tanggal tidak valid, validasi dilakukan di controller.
                }
            })
            ->orderBy('date')
            ->get();

        $journalCollection = TeachingJournal::query()
            ->with(['guru', 'subject'])
            ->whereBetween('tanggal', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->orderBy('tanggal')
            ->get();

        return [
            'period' => [
                'month' => $period['month'],
                'year' => $period['year'],
                'range' => [$period['start']->toDateString(), $period['end']->toDateString()],
                'label' => $period['label'],
            ],
            'attendance' => $this->formatAttendance($attendanceCollection),
            'journals' => $this->formatJournals($journalCollection),
        ];
    }

    /**
     * Proses ringkasan absensi siswa.
     */
    protected function formatAttendance(Collection $attendances): array
    {
        $perStudent = $attendances
            ->groupBy('student_id')
            ->map(function (Collection $entries) {
                $student = $entries->first()->student;
                $totals = [
                    'hadir' => $entries->where('status', 'hadir')->count(),
                    'izin' => $entries->where('status', 'izin')->count(),
                    'sakit' => $entries->where('status', 'sakit')->count(),
                    'alpa' => $entries->where('status', 'alpa')->count(),
                ];

                return [
                    'student' => [
                        'id' => $student?->id,
                        'name' => $student?->name,
                        'classroom' => $student?->classroom?->name,
                    ],
                    'totals' => $totals,
                    'records' => $entries->map(fn ($attendance) => [
                        'date' => $attendance->date?->toDateString(),
                        'status' => $attendance->status,
                    ])->values(),
                ];
            })
            ->values()
            ->toArray();

        $aggregate = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
            'total_records' => $attendances->count(),
            'total_students' => $attendances->pluck('student_id')->unique()->count(),
        ];

        return [
            'summary' => $aggregate,
            'by_students' => $perStudent,
        ];
    }

    /**
     * Proses ringkasan jurnal mengajar.
     */
    protected function formatJournals(Collection $journals): array
    {
        $items = $journals->map(function ($journal) {
            $durationMinutes = Carbon::parse($journal->jam_mulai)->diffInMinutes(Carbon::parse($journal->jam_selesai));
            $subjectName = $journal->subject?->name ?? $journal->mata_pelajaran;

            return [
                'id' => $journal->id,
                'tanggal' => $journal->tanggal?->toDateString(),
                'mata_pelajaran' => $subjectName,
                'jam_mulai' => Carbon::parse($journal->jam_mulai)->format('H:i'),
                'jam_selesai' => Carbon::parse($journal->jam_selesai)->format('H:i'),
                'durasi_menit' => $durationMinutes,
                'topik' => $journal->topik,
                'catatan' => $journal->catatan,
                'guru' => [
                    'id' => $journal->guru?->id,
                    'name' => $journal->guru?->name,
                    'email' => $journal->guru?->email,
                ],
                'subject' => [
                    'id' => $journal->subject?->id,
                    'name' => $subjectName,
                ],
            ];
        })->values();

        $totalMinutes = $items->sum('durasi_menit');

        $perTeacher = $items
            ->groupBy(fn ($item) => $item['guru']['id'] ?? 'unknown')
            ->map(function ($entries) {
                $first = $entries->first();

                return [
                    'guru' => $first['guru'],
                    'total_pertemuan' => $entries->count(),
                    'total_durasi_menit' => $entries->sum('durasi_menit'),
                ];
            })
            ->values()
            ->toArray();

        return [
            'summary' => [
                'total_pertemuan' => $items->count(),
                'total_durasi_menit' => $totalMinutes,
            ],
            'records' => $items,
            'by_teachers' => $perTeacher,
        ];
    }

    /**
     * Tentukan periode laporan.
     */
    protected function resolvePeriod(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return [
            'year' => $start->year,
            'month' => $start->month,
            'start' => $start,
            'end' => $end,
            'label' => $start->translatedFormat('F Y'),
        ];
    }

    public static function resolveForDashboard(int $year, int $month)
    {
        $service = new static;
        $period = $service->resolvePeriod($year, $month);

        $journals = TeachingJournal::query()
            ->with(['guru', 'subject'])
            ->whereBetween('tanggal', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->get();

        return $service->prepareTeacherDurations($journals);
    }

    public static function resolveForTeacherSummary(?int $teacherId, ?string $nameFilter, ?string $startDate, ?string $endDate): Collection
    {
        $query = TeachingJournal::query()->with(['guru', 'subject']);

        if ($teacherId) {
            $query->where('guru_id', $teacherId);
        }

        if ($nameFilter) {
            $query->whereHas('guru', fn ($q) => $q->where('name', 'like', "%{$nameFilter}%"));
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', Carbon::parse($endDate));
        }

        $journals = $query->get();

        $service = new static;

        return $service->prepareTeacherDurations($journals);
    }

    protected function prepareTeacherDurations(Collection $journals): Collection
    {
        $formatted = $this->formatJournals($journals);

        $records = collect($formatted['records'] ?? []);

        $recordsByTeacher = $records->groupBy(fn ($item) => $item['guru']['id'] ?? 'unknown');

        return collect($formatted['by_teachers'] ?? [])->map(function ($row) use ($recordsByTeacher) {
            $teacherId = $row['guru']['id'] ?? 'unknown';
            $row['records'] = $recordsByTeacher->get($teacherId, collect())->values()->all();

            if (! isset($row['guru']['name']) || $row['guru']['name'] === null) {
                $row['guru']['name'] = 'Tidak diketahui';
            }

            return $row;
        })->sortByDesc('total_durasi_menit')->values();
    }
}
