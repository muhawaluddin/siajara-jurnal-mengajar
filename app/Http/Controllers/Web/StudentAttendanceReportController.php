<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Services\StudentAttendanceSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAttendanceReportController extends Controller
{
    public function __construct(private readonly StudentAttendanceSummaryService $service)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $studentId = $validated['student_id'] ?? null;
        $subjectId = $validated['subject_id'] ?? null;
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        $students = Student::query()
            ->with('classroom')
            ->orderBy('name')
            ->get(['id', 'name', 'classroom_id']);

        $subjects = Subject::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $student = $studentId
            ? $students->firstWhere('id', $studentId)
            : null;
        $subject = $subjectId
            ? $subjects->firstWhere('id', $subjectId)
            : null;

        $emptySummary = [
            'totals' => array_merge(array_fill_keys($this->service->statuses(), 0), ['total_records' => 0]),
            'presence_percentage' => 0,
            'per_subject' => [],
            'recent_records' => [],
            'status_keys' => $this->service->statuses(),
        ];

        $summary = $student
            ? $this->service->summarize($studentId, $subjectId, $startDate, $endDate)
            : $emptySummary;

        return view('admin.reports.student-attendance', [
            'students' => $students,
            'subjects' => $subjects,
            'student' => $student,
            'subject' => $subject,
            'selectedStudentId' => $studentId,
            'selectedSubjectId' => $subjectId,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
        ]);
    }
}
