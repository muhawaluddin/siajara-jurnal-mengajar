<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\TeachingJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    public function index(Request $request): View
    {
        $journals = TeachingJournal::query()
            ->with('subject')
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
        $subjects = Subject::orderBy('name')->get();

        return view('teaching-journals.create', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);
        $subject = Subject::find($validated['subject_id']);

        TeachingJournal::create(array_merge($validated, [
            'guru_id' => Auth::id(),
            'mata_pelajaran' => $subject?->name,
        ]));

        return redirect()->route('web.teaching-journals.index')->with('status', 'Jurnal mengajar berhasil ditambahkan.');
    }

    public function edit(TeachingJournal $teachingJournal): View
    {
        $this->authorizeJournal($teachingJournal);

        $subjects = Subject::orderBy('name')->get();

        return view('teaching-journals.edit', compact('teachingJournal', 'subjects'));
    }

    public function update(Request $request, TeachingJournal $teachingJournal): RedirectResponse
    {
        $this->authorizeJournal($teachingJournal);

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);
        $subject = Subject::find($validated['subject_id']);

        $teachingJournal->update(array_merge($validated, [
            'mata_pelajaran' => $subject?->name,
        ]));

        return redirect()->route('web.teaching-journals.index')->with('status', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingJournal $teachingJournal): RedirectResponse
    {
        $this->authorizeJournal($teachingJournal);

        $teachingJournal->delete();

        return redirect()->route('web.teaching-journals.index')->with('status', 'Jurnal mengajar berhasil dihapus.');
    }

    protected function authorizeJournal(TeachingJournal $teachingJournal): void
    {
        abort_if($teachingJournal->guru_id !== Auth::id(), 403);
    }
}
