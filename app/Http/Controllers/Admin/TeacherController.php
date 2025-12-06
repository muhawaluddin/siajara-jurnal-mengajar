<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = User::query()
            ->where('role', UserRole::Guru)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        $subjects = Subject::orderBy('name')->get();

        return view('admin.teachers.create', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        $teacher = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Guru,
        ]);

        $teacher->subjects()->sync($validated['subject_ids'] ?? []);

        return redirect()->route('admin.teachers.index')->with('status', 'Guru baru berhasil ditambahkan.');
    }

    public function edit(User $teacher): View
    {
        abort_unless($teacher->role === UserRole::Guru, 404);

        $subjects = Subject::orderBy('name')->get();

        return view('admin.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === UserRole::Guru, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$teacher->id],
            'password' => ['nullable', 'string', 'min:8'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        $teacher->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $teacher->password,
        ]);

        $teacher->subjects()->sync($validated['subject_ids'] ?? []);

        return redirect()->route('admin.teachers.index')->with('status', 'Data guru berhasil diperbarui.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_unless($teacher->role === UserRole::Guru, 404);

        if (auth()->id() === $teacher->id) {
            return redirect()->route('admin.teachers.index')->withErrors(['teacher' => 'Tidak dapat menghapus akun yang sedang digunakan.']);
        }

        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('status', 'Guru berhasil dihapus.');
    }
}
