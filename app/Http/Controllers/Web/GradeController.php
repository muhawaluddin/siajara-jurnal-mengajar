<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user();

        $assessments = Assessment::query()
            ->with(['subject', 'classroom'])
            ->withCount('scores')
            ->where('teacher_id', $teacher->id)
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->input('subject_id')))
            ->when($request->filled('classroom_id'), fn ($query) => $query->where('classroom_id', $request->input('classroom_id')))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $subjects = $teacher->subjects()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();

        return view('grades.index', compact('assessments', 'subjects', 'classrooms'));
    }

    public function create(): View
    {
        $teacher = Auth::user();
        $subjects = $teacher->subjects()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::with('classroom')->orderBy('name')->get();

        return view('grades.create', compact('subjects', 'classrooms', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user();

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:pertemuan,uts,uas'],
            'pertemuan_ke' => ['nullable', 'integer', 'min:1', 'max:50'],
            'tanggal' => ['nullable', 'date'],
            'scores' => ['nullable', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        abort_unless($this->teacherOwnsSubject($teacher->id, (int) $validated['subject_id']), 403, 'Anda hanya dapat memasukkan nilai untuk mapel yang diampu.');

        $studentIds = Student::where('classroom_id', $validated['classroom_id'])->pluck('id')->all();

        DB::transaction(function () use ($validated, $teacher, $studentIds) {
            $assessment = Assessment::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $validated['subject_id'],
                'classroom_id' => $validated['classroom_id'],
                'title' => $validated['title'],
                'type' => $validated['type'],
                'pertemuan_ke' => $validated['type'] === 'pertemuan' ? $validated['pertemuan_ke'] : null,
                'tanggal' => $validated['tanggal'],
            ]);

            $this->syncScores($assessment, $studentIds, $validated['scores'] ?? []);
        });

        return redirect()->route('web.grades.index')->with('status', 'Penilaian berhasil disimpan.');
    }

    public function edit(Assessment $assessment): View
    {
        $this->authorizeAssessment($assessment);

        $assessment->load('scores');

        $teacher = Auth::user();
        $subjects = $teacher->subjects()->orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $students = Student::with('classroom')->orderBy('name')->get();

        return view('grades.edit', compact('assessment', 'subjects', 'classrooms', 'students'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($assessment);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:pertemuan,uts,uas'],
            'pertemuan_ke' => ['nullable', 'integer', 'min:1', 'max:50'],
            'tanggal' => ['nullable', 'date'],
            'scores' => ['nullable', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        abort_unless($this->teacherOwnsSubject(Auth::id(), (int) $validated['subject_id']), 403, 'Anda hanya dapat memasukkan nilai untuk mapel yang diampu.');

        $studentIds = Student::where('classroom_id', $validated['classroom_id'])->pluck('id')->all();

        DB::transaction(function () use ($assessment, $validated, $studentIds) {
            $assessment->update([
                'subject_id' => $validated['subject_id'],
                'classroom_id' => $validated['classroom_id'],
                'title' => $validated['title'],
                'type' => $validated['type'],
                'pertemuan_ke' => $validated['type'] === 'pertemuan' ? $validated['pertemuan_ke'] : null,
                'tanggal' => $validated['tanggal'],
            ]);

            $this->syncScores($assessment, $studentIds, $validated['scores'] ?? []);
        });

        return redirect()->route('web.grades.index')->with('status', 'Penilaian berhasil diperbarui.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorizeAssessment($assessment);

        $assessment->delete();

        return redirect()->route('web.grades.index')->with('status', 'Penilaian berhasil dihapus.');
    }

    protected function authorizeAssessment(Assessment $assessment): void
    {
        abort_if($assessment->teacher_id !== Auth::id(), 403);
    }

    protected function syncScores(Assessment $assessment, array $studentIds, array $scores): void
    {
        $allowed = array_map('intval', $studentIds);
        $cleanedScores = $this->normalizeScores($scores, $allowed);

        $assessment->scores()->whereNotIn('student_id', $allowed)->delete();

        foreach ($allowed as $studentId) {
            if (array_key_exists($studentId, $cleanedScores)) {
                AssessmentScore::updateOrCreate(
                    [
                        'assessment_id' => $assessment->id,
                        'student_id' => $studentId,
                    ],
                    ['score' => $cleanedScores[$studentId]]
                );
            } else {
                $assessment->scores()->where('student_id', $studentId)->delete();
            }
        }
    }

    /**
     * Filter dan normalisasi skor yang boleh disimpan.
     *
     * @param array<string|int, mixed> $scores
     * @param array<int, int> $allowedStudentIds
     * @return array<int, float>
     */
    protected function normalizeScores(array $scores, array $allowedStudentIds): array
    {
        $allowedMap = array_fill_keys($allowedStudentIds, true);
        $normalized = [];

        foreach ($scores as $studentId => $score) {
            $studentId = (int) $studentId;

            if (! isset($allowedMap[$studentId])) {
                continue;
            }

            if ($score === null || $score === '') {
                continue;
            }

            if (is_string($score)) {
                $score = str_replace(',', '.', $score);
            }

            if (is_numeric($score)) {
                $normalized[$studentId] = round((float) $score, 2);
            }
        }

        return $normalized;
    }

    protected function teacherOwnsSubject(int $teacherId, int $subjectId): bool
    {
        return Subject::whereHas('teachers', function ($query) use ($teacherId) {
            $query->where('users.id', $teacherId);
        })->where('id', $subjectId)->exists();
    }
}
