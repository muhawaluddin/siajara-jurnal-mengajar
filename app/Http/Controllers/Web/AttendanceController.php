<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\TeachingJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    private array $statusOptions = ['hadir', 'izin', 'sakit', 'alpa'];

    public function index(Request $request): View
    {
        $user = $request->user();

        $classrooms = Classroom::query()
            ->orderBy('name')
            ->get();

        $selectedClassroomId = (int) $request->input('class');
        if ($selectedClassroomId === 0 && $classrooms->isNotEmpty()) {
            $selectedClassroomId = $classrooms->first()->id;
        }

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : today();

        $students = collect();
        $attendanceMap = collect();

        $selectedJournalId = $request->input('teaching_journal_id')
            ? (int) $request->input('teaching_journal_id')
            : null;

        if ($selectedClassroomId > 0) {
            $students = Student::query()
                ->with('classroom')
                ->where('classroom_id', $selectedClassroomId)
                ->orderBy('name')
                ->get();

            if ($students->isNotEmpty()) {
                $attendanceMap = Attendance::query()
                    ->with('teachingJournal')
                    ->whereDate('date', $date)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->when($selectedJournalId, fn ($query) => $query->where('teaching_journal_id', $selectedJournalId))
                    ->when(! $selectedJournalId, fn ($query) => $query->whereNull('teaching_journal_id'))
                    ->get()
                    ->keyBy('student_id');
            }
        }

        $availableJournals = TeachingJournal::query()
            ->with('subject')
            ->when($user && ! $user->isAdmin(), fn ($query) => $query->where('guru_id', $user->id))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('tanggal', $date))
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        if ($selectedJournalId === null && $attendanceMap->isNotEmpty()) {
            $selectedJournalId = $attendanceMap->first()->teaching_journal_id;
        }

        return view('attendances.index', [
            'classrooms' => $classrooms,
            'selectedClassroomId' => $selectedClassroomId,
            'selectedClassroom' => $classrooms->firstWhere('id', $selectedClassroomId),
            'date' => $date,
            'students' => $students,
            'attendanceMap' => $attendanceMap,
            'statusOptions' => $this->statusOptions,
            'availableJournals' => $availableJournals,
            'selectedJournalId' => $selectedJournalId,
        ]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class' => ['required', 'integer', 'exists:classrooms,id'],
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', Rule::in($this->statusOptions)],
            'teaching_journal_id' => ['nullable', 'integer', 'exists:teaching_journals,id'],
        ]);

        $classroomId = (int) $validated['class'];
        $studentIds = array_map('intval', array_keys($validated['statuses']));

        $students = Student::query()
            ->whereIn('id', $studentIds)
            ->where('classroom_id', $classroomId)
            ->get();

        if ($students->count() !== count($studentIds)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['statuses' => 'Beberapa siswa tidak valid untuk kelas yang dipilih.']);
        }

        $date = Carbon::parse($validated['date'])->startOfDay();

        DB::transaction(function () use ($validated, $students, $date) {
            foreach ($students as $student) {
                $status = Arr::get($validated['statuses'], (string) $student->id);

                Attendance::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'date' => $date->toDateString(),
                        'teaching_journal_id' => $validated['teaching_journal_id'] ?? null,
                    ],
                    [
                        'status' => $status,
                    ]
                );
            }
        });

        return redirect()
            ->route('web.attendances.index', [
                'class' => $classroomId,
                'date' => $date->toDateString(),
                'teaching_journal_id' => $validated['teaching_journal_id'] ?? null,
            ])
            ->with('status', 'Absensi kelas berhasil diperbarui.');
    }

    public function history(Request $request): View
    {
        $attendances = Attendance::query()
            ->with(['student.classroom', 'teachingJournal'])
            ->when($request->filled('date'), fn ($query) => $query->whereDate('date', $request->date('date')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('class'), fn ($query) => $query->whereHas('student', fn ($sub) => $sub->where('classroom_id', (int) $request->input('class'))))
            ->when($request->filled('journal'), fn ($query) => $query->where('teaching_journal_id', $request->input('journal')))
            ->orderByDesc('date')
            ->paginate(10)
            ->withQueryString();

        $statusOptions = $this->statusOptions;
        $classrooms = Classroom::query()
            ->orderBy('name')
            ->get();
        $journals = TeachingJournal::query()
            ->when($request->user() && ! $request->user()->isAdmin(), fn ($query) => $query->where('guru_id', $request->user()->id))
            ->orderByDesc('tanggal')
            ->limit(100)
            ->get();

        return view('attendances.history', [
            'attendances' => $attendances,
            'statusOptions' => $statusOptions,
            'classrooms' => $classrooms,
            'journals' => $journals,
        ]);
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->back()->with('status', 'Data absensi berhasil dihapus.');
    }
}
