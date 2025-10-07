<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = ['student_id', 'date', 'status', 'teaching_journal_id'];

    /** @var array<string, string> */
    protected $casts = ['date' => 'date'];

    /**
     * Relasi ke siswa yang diabsen.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teachingJournal(): BelongsTo
    {
        return $this->belongsTo(TeachingJournal::class);
    }
}
