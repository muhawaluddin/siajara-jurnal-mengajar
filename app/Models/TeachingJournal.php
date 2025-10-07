<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class TeachingJournal extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'guru_id',
        'subject_id',
        'mata_pelajaran',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'topik',
        'catatan',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    /**
     * Relasi ke guru (user) pencatat jurnal.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
