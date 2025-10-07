<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeachingJournal\StoreTeachingJournalRequest;
use App\Http\Requests\TeachingJournal\UpdateTeachingJournalRequest;
use App\Http\Resources\TeachingJournalResource;
use App\Models\TeachingJournal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeachingJournalController extends Controller
{
    /** Tampilkan jurnal mengajar dengan filter dinamis. */
    public function index(Request $request)
    {
        $journals = TeachingJournal::query()
            ->with('guru')
            ->when($request->filled('guru_id'), fn ($query) =>
                $query->where('guru_id', $request->integer('guru_id'))
            )
            ->when($request->filled('mata_pelajaran'), fn ($query) =>
                $query->where('mata_pelajaran', 'like', '%' . $request->string('mata_pelajaran') . '%')
            )
            ->when($request->filled('month') && $request->filled('year'), function ($query) use ($request) {
                $start = Carbon::create($request->integer('year'), $request->integer('month'), 1)->startOfMonth();
                $end = (clone $start)->endOfMonth();
                $query->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);
            })
            ->orderByDesc('tanggal')
            ->paginate($request->integer('per_page', 15));

        return TeachingJournalResource::collection($journals);
    }

    /** Simpan jurnal mengajar baru. */
    public function store(StoreTeachingJournalRequest $request): JsonResponse
    {
        $journal = TeachingJournal::query()->create($request->validated());

        return TeachingJournalResource::make($journal->load('guru'))
            ->response()
            ->setStatusCode(201);
    }

    /** Perlihatkan detail jurnal. */
    public function show(TeachingJournal $teachingJournal): TeachingJournalResource
    {
        return TeachingJournalResource::make($teachingJournal->load('guru'));
    }

    /** Perbarui jurnal mengajar. */
    public function update(UpdateTeachingJournalRequest $request, TeachingJournal $teachingJournal): TeachingJournalResource
    {
        $teachingJournal->update($request->validated());

        return TeachingJournalResource::make($teachingJournal->load('guru'));
    }

    /** Hapus jurnal mengajar. */
    public function destroy(TeachingJournal $teachingJournal): JsonResponse
    {
        $teachingJournal->delete();

        return response()->json(null, 204);
    }
}
