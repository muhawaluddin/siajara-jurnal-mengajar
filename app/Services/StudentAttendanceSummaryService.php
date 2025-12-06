<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentAttendanceSummaryService
{
    /** @var array<int, string> */
    protected array $statuses = ['hadir', 'izin', 'pulang izin', 'sakit', 'pulang sakit', 'alpa'];

    /**
    * Ringkas kehadiran berdasarkan siswa, mapel, dan (opsional) rentang tanggal.
    */
    public function summarize(?int $studentId = null, ?int $subjectId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Attendance::query()
            ->with(['student.classroom', 'teachingJournal.subject'])
            ->when($studentId, fn ($q) => $q->where('student_id', $studentId))
            ->when($subjectId, fn ($q) => $q->whereHas('teachingJournal', fn ($sub) => $sub->where('subject_id', $subjectId)))
            ->when($startDate, fn ($q) => $q->whereDate('date', '>=', Carbon::parse($startDate)))
            ->when($endDate, fn ($q) => $q->whereDate('date', '<=', Carbon::parse($endDate)))
            ->orderByDesc('date');

        /** @var \Illuminate\Support\Collection<int, \App\Models\Attendance> $attendances */
        $attendances = $query->get();

        $totals = $this->blankTotals();
        foreach ($attendances as $attendance) {
            if (array_key_exists($attendance->status, $totals)) {
                $totals[$attendance->status]++;
            }
        }

        $totalRecords = $attendances->count();
        $presencePercentage = $totalRecords > 0
            ? round(($totals['hadir'] / $totalRecords) * 100, 1)
            : 0.0;

        $perSubject = $this->summarizePerSubject($attendances);

        $recentRecords = $attendances
            ->take(8)
            ->map(function (Attendance $attendance) {
                $subjectName = $attendance->teachingJournal?->subject?->name
                    ?? $attendance->teachingJournal?->mata_pelajaran
                    ?? '-';

                return [
                    'date' => $attendance->date?->toDateString(),
                    'status' => $attendance->status,
                    'subject' => $subjectName,
                    'journal_id' => $attendance->teaching_journal_id,
                ];
            })
            ->values()
            ->all();

        return [
            'totals' => array_merge($totals, ['total_records' => $totalRecords]),
            'presence_percentage' => $presencePercentage,
            'per_subject' => $perSubject,
            'recent_records' => $recentRecords,
            'status_keys' => $this->statuses,
        ];
    }

    /**
     * Dapatkan daftar status yang didukung.
     *
     * @return array<int, string>
     */
    public function statuses(): array
    {
        return $this->statuses;
    }

    /**
     * Ringkas kehadiran per mata pelajaran.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Attendance>  $attendances
     * @return array<int, array<string, mixed>>
     */
    protected function summarizePerSubject(Collection $attendances): array
    {
        return $attendances
            ->groupBy(function (Attendance $attendance) {
                return $attendance->teachingJournal?->subject?->id ?? 'tanpa-mapel';
            })
            ->map(function (Collection $entries) {
                $totals = $this->blankTotals();
                foreach ($entries as $attendance) {
                    if (array_key_exists($attendance->status, $totals)) {
                        $totals[$attendance->status]++;
                    }
                }

                $totalRecords = $entries->count();
                $presencePercentage = $totalRecords > 0
                    ? round(($totals['hadir'] / $totalRecords) * 100, 1)
                    : 0.0;

                $first = $entries->first();

                $subjectName = $first?->teachingJournal?->subject?->name
                    ?? $first?->teachingJournal?->mata_pelajaran
                    ?? 'Tidak diketahui';

                return [
                    'subject' => [
                        'id' => $first?->teachingJournal?->subject?->id,
                        'name' => $subjectName,
                    ],
                    'totals' => array_merge($totals, ['total_records' => $totalRecords]),
                    'presence_percentage' => $presencePercentage,
                    'latest_date' => optional($entries->max('date'))->toDateString(),
                ];
            })
            ->sortByDesc('presence_percentage')
            ->values()
            ->all();
    }

    /**
     * Siapkan template kosong untuk total status.
     */
    protected function blankTotals(): array
    {
        $totals = [];
        foreach ($this->statuses as $status) {
            $totals[$status] = 0;
        }

        return $totals;
    }
}
