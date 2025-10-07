<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MonthlyReportExport;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Services\MonthlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private readonly MonthlyReportService $service)
    {
    }

    public function index(Request $request): View
    {
        $validated = $this->validatePeriod($request);

        $classrooms = Classroom::orderBy('name')->get(['id', 'name']);
        $selectedClassroomName = $classrooms->firstWhere('id', $validated['classroom_id'] ?? null)?->name;

        $report = $this->service->generate(
            $validated['year'],
            $validated['month'],
            $validated['classroom_id'] ?? null,
            $validated['student'] ?? null,
            $validated['date'] ?? null,
        );
        $report['filters'] = [
            'classroom_name' => $selectedClassroomName,
            'student_keyword' => $validated['student'] ?? null,
            'specific_date' => $validated['date'] ?? null,
        ];

        return view('admin.reports.index', [
            'report' => $report,
            'selectedMonth' => $validated['month'],
            'selectedYear' => $validated['year'],
            'classrooms' => $classrooms,
            'selectedClassroomId' => $validated['classroom_id'] ?? null,
            'selectedClassroomName' => $selectedClassroomName,
            'selectedStudentKeyword' => $validated['student'] ?? null,
            'selectedSpecificDate' => $validated['date'] ?? null,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $validated = $this->validatePeriod($request);
        $classroomName = Classroom::find($validated['classroom_id'] ?? null)?->name;

        $report = $this->service->generate(
            $validated['year'],
            $validated['month'],
            $validated['classroom_id'] ?? null,
            $validated['student'] ?? null,
            $validated['date'] ?? null,
        );
        $report['filters'] = [
            'classroom_name' => $classroomName,
            'student_keyword' => $validated['student'] ?? null,
            'specific_date' => $validated['date'] ?? null,
        ];

        $pdf = Pdf::loadView('reports.monthly', $report);
        $filename = sprintf('laporan-bulanan-%s.pdf', strtolower(str_replace(' ', '-', $report['period']['label'])));

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $validated = $this->validatePeriod($request);
        $classroomName = Classroom::find($validated['classroom_id'] ?? null)?->name;

        $report = $this->service->generate(
            $validated['year'],
            $validated['month'],
            $validated['classroom_id'] ?? null,
            $validated['student'] ?? null,
            $validated['date'] ?? null,
        );
        $report['filters'] = [
            'classroom_name' => $classroomName,
            'student_keyword' => $validated['student'] ?? null,
            'specific_date' => $validated['date'] ?? null,
        ];

        $filename = sprintf('laporan-bulanan-%s.xlsx', strtolower(str_replace(' ', '-', $report['period']['label'])));

        return Excel::download(new MonthlyReportExport($report), $filename);
    }

    protected function validatePeriod(Request $request): array
    {
        $defaults = [
            'month' => (int) Carbon::now()->month,
            'year' => (int) Carbon::now()->year,
        ];

        $data = $request->all();

        if (! array_key_exists('month', $data)) {
            $request->merge(['month' => $defaults['month']]);
        }

        if (! array_key_exists('year', $data)) {
            $request->merge(['year' => $defaults['year']]);
        }

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'student' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
        ]);

        if (array_key_exists('student', $validated)) {
            $validated['student'] = $validated['student'] !== null && trim($validated['student']) !== ''
                ? trim($validated['student'])
                : null;
        }

        if (array_key_exists('date', $validated) && $validated['date'] !== null) {
            $validated['date'] = Carbon::parse($validated['date'])->toDateString();
        }

        if (array_key_exists('classroom_id', $validated) && $validated['classroom_id'] !== null) {
            $validated['classroom_id'] = (int) $validated['classroom_id'];
        }

        return $validated;
    }
}
