<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\MonthlyReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TeacherReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $teacherId = $request->input('teacher_id') ? (int) $request->input('teacher_id') : null;
        $nameFilter = $request->filled('name') ? $request->string('name')->toString() : null;

        $teacherSummaries = MonthlyReportService::resolveForTeacherSummary(
            $teacherId,
            $nameFilter,
            $startDate,
            $endDate
        );

        $totalMinutes = $teacherSummaries->sum('total_durasi_menit');

        $teachers = User::query()
            ->select('id', 'name')
            ->where('role', UserRole::Guru)
            ->orderBy('name')
            ->get();

        return view('admin.reports.teacher', [
            'summaries' => $teacherSummaries,
            'teachers' => $teachers,
            'filters' => [
                'teacher_id' => $teacherId,
                'name' => $nameFilter,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'totalMinutes' => $totalMinutes,
        ]);
    }
}
