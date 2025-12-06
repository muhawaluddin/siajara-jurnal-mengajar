<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TeachingJournalResource extends JsonResource
{
    /** Format response jurnal mengajar. */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guru_id' => $this->guru_id,
            'classroom_id' => $this->classroom_id,
            'mata_pelajaran' => $this->mata_pelajaran,
            'tanggal' => $this->tanggal?->toDateString(),
            'jam_mulai' => $this->jam_mulai?->format('H:i'),
            'jam_selesai' => $this->jam_selesai?->format('H:i'),
            'topik' => $this->topik,
            'catatan' => $this->catatan,
            'documentation_url' => $this->documentation_path
                ? Storage::disk('public')->url($this->documentation_path)
                : null,
            'classroom' => $this->whenLoaded('classroom', fn () => [
                'id' => $this->classroom?->id,
                'name' => $this->classroom?->name,
            ]),
            'guru' => $this->whenLoaded('guru', fn () => [
                'id' => $this->guru?->id,
                'name' => $this->guru?->name,
                'email' => $this->guru?->email,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
