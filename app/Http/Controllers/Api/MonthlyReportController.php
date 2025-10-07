<?php

namespace App\Http\Controllers\Api;

use App\Exports\MonthlyReportExport;
use App\Http\Controllers\Controller;
use App\Services\MonthlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyReportController extends Controller
{
    public function __construct(private readonly MonthlyReportService $service)
    {
        //
    }

    /** Tampilkan ringkasan laporan bulanan dalam bentuk JSON. */
    public function show(Request $request): JsonResponse
    {
        $validated = $this->validatePeriod($request);
        $report = $this->service->generate($validated['year'], $validated['month']);

        return response()->json($report);
    }

    /** Unduh laporan bulanan dalam format PDF. */
    public function exportPdf(Request $request): Response
    {
        $validated = $this->validatePeriod($request);
        $report = $this->service->generate($validated['year'], $validated['month']);

        $pdf = Pdf::loadView('reports.monthly', $report);
        $filename = sprintf('laporan-bulanan-%s.pdf', strtolower(str_replace(' ', '-', $report['period']['label'])));

        return $pdf->download($filename);
    }

    /** Unduh laporan bulanan dalam format Excel. */
    public function exportExcel(Request $request)
    {
        $validated = $this->validatePeriod($request);
        $report = $this->service->generate($validated['year'], $validated['month']);

        $filename = sprintf('laporan-bulanan-%s.xlsx', strtolower(str_replace(' ', '-', $report['period']['label'])));

        return Excel::download(new MonthlyReportExport($report), $filename);
    }

    /** Validasi parameter periode laporan. */
    protected function validatePeriod(Request $request): array
    {
        return $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);
    }
}
