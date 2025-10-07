<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeachingJournalResource extends JsonResource
{
    /** Format response jurnal mengajar. */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guru_id' => $this->guru_id,
            'mata_pelajaran' => $this->mata_pelajaran,
            'tanggal' => $this->tanggal?->toDateString(),
            'jam_mulai' => $this->jam_mulai?->format('H:i'),
            'jam_selesai' => $this->jam_selesai?->format('H:i'),
            'topik' => $this->topik,
            'catatan' => $this->catatan,
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
