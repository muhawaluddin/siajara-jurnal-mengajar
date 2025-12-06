<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentScore;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeReportController extends Controller
{
    public function index(Request $request): View
    {
        $subjectId = $request->input('subject_id');
        $classroomId = $request->input('classroom_id');
        $search = $request->string('search')->toString();

        $students = Student::query()
            ->with(['classroom'])
            ->with(['assessmentScores' => function ($query) use ($subjectId) {
                $query->with(['assessment.subject', 'assessment.teacher'])
                    ->when($subjectId, fn ($subQuery) => $subQuery->whereHas('assessment', fn ($q) => $q->where('subject_id', $subjectId)));
            }])
            ->when($request->filled('classroom_id'), fn ($query) => $query->where('classroom_id', $classroomId))
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($subjectId, fn ($query) => $query->whereHas('assessmentScores', fn ($scores) => $scores->whereHas('assessment', fn ($sub) => $sub->where('subject_id', $subjectId))))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $scoresBySubject = AssessmentScore::query()
            ->with([
                'student.classroom',
                'assessment.subject',
                'assessment.teacher',
                'assessment.classroom',
            ])
            ->when($subjectId, fn ($query) => $query->whereHas('assessment', fn ($sub) => $sub->where('subject_id', $subjectId)))
            ->when($request->filled('classroom_id'), fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('classroom_id', $classroomId)))
            ->when($request->filled('search'), fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('name', 'like', "%{$search}%")))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn ($score) => $score->assessment?->subject?->name ?? 'Tanpa Mapel');

        $classrooms = Classroom::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.grades.index', compact('students', 'classrooms', 'subjects', 'scoresBySubject'));
    }
}
