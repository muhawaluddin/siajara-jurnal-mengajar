<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(Request $request): View
    {
        $classrooms = Classroom::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', "%{$request->string('search')}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function create(): View
    {
        return view('admin.classrooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:classrooms,name'],
        ]);

        Classroom::create($validated);

        return redirect()->route('admin.classrooms.index')->with('status', 'Kelas baru berhasil dibuat.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('admin.classrooms.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:classrooms,name,'.$classroom->id],
        ]);

        $classroom->update($validated);

        return redirect()->route('admin.classrooms.index')->with('status', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

        return redirect()->route('admin.classrooms.index')->with('status', 'Kelas berhasil dihapus.');
    }
}
