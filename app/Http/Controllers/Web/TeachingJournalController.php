<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\TeachingJournal;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    public function index(Request $request): View
    {
        $journals = TeachingJournal::query()
            ->with(['subject', 'classroom'])
            ->where('guru_id', Auth::id())
            ->when($request->filled('month'), function ($query) use ($request) {
                $month = (int) $request->input('month');
                $query->whereMonth('tanggal', $month);
            })
            ->orderByDesc('tanggal')
            ->paginate(10)
            ->withQueryString();

        return view('teaching-journals.index', compact('journals'));
    }

    public function create(): View
    {
        $classrooms = Classroom::orderBy('name')->get();
        $subjects = Auth::user()->subjects()->orderBy('name')->get();

        return view('teaching-journals.create', compact('subjects', 'classrooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'documentation' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        unset($validated['documentation']);

        abort_unless($this->teacherOwnsSubject((int) $validated['subject_id']), 403, 'Anda hanya dapat mencatat jurnal untuk mapel yang diampu.');

        $subject = Subject::find($validated['subject_id']);
        $path = null;

        if ($request->hasFile('documentation')) {
            $path = ImageOptimizer::storeWithFallback($request->file('documentation'), 'teaching-journals');
        }

        TeachingJournal::create(array_merge($validated, [
            'guru_id' => Auth::id(),
            'mata_pelajaran' => $subject?->name,
            'documentation_path' => $path,
        ]));

        return redirect()->route('web.teaching-journals.index')->with('status', 'Jurnal mengajar berhasil ditambahkan.');
    }

    public function edit(TeachingJournal $teachingJournal): View
    {
        $this->authorizeJournal($teachingJournal);

        $classrooms = Classroom::orderBy('name')->get();
        $subjects = Auth::user()->subjects()->orderBy('name')->get();

        return view('teaching-journals.edit', compact('teachingJournal', 'subjects', 'classrooms'));
    }

    public function update(Request $request, TeachingJournal $teachingJournal): RedirectResponse
    {
        $this->authorizeJournal($teachingJournal);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'documentation' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        unset($validated['documentation']);

        abort_unless($this->teacherOwnsSubject((int) $validated['subject_id']), 403, 'Anda hanya dapat mencatat jurnal untuk mapel yang diampu.');

        $subject = Subject::find($validated['subject_id']);
        $path = $teachingJournal->documentation_path;

        if ($request->hasFile('documentation')) {
            $path = ImageOptimizer::storeWithFallback($request->file('documentation'), 'teaching-journals');
            if ($teachingJournal->documentation_path) {
                Storage::disk('public')->delete($teachingJournal->documentation_path);
            }
        }

        $teachingJournal->update(array_merge($validated, [
            'mata_pelajaran' => $subject?->name,
            'documentation_path' => $path,
        ]));

        return redirect()->route('web.teaching-journals.index')->with('status', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingJournal $teachingJournal): RedirectResponse
    {
        $this->authorizeJournal($teachingJournal);

        if ($teachingJournal->documentation_path) {
            Storage::disk('public')->delete($teachingJournal->documentation_path);
        }

        $teachingJournal->delete();

        return redirect()->route('web.teaching-journals.index')->with('status', 'Jurnal mengajar berhasil dihapus.');
    }

    protected function authorizeJournal(TeachingJournal $teachingJournal): void
    {
        abort_if($teachingJournal->guru_id !== Auth::id(), 403);
    }

    protected function teacherOwnsSubject(int $subjectId): bool
    {
        return Subject::where('id', $subjectId)
            ->whereHas('teachers', fn ($query) => $query->where('users.id', Auth::id()))
            ->exists();
    }
}
