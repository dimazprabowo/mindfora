<?php

namespace App\Models;

use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class TestProgramWeek extends Model
{
    use HasEncryptedRouteKey, HasFactory;

    protected $fillable = [
        'test_program_id',
        'week_number',
        'title',
        'is_active',
        'threshold_low_max',
        'threshold_medium_max',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'threshold_low_max' => 'integer',
            'threshold_medium_max' => 'integer',
        ];
    }

    // Relationships
    public function program(): BelongsTo
    {
        return $this->belongsTo(TestProgram::class, 'test_program_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_program_week_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    /**
     * Tanggal mulai minggu ini berdasarkan start_date assignment.
     * opens_at = assignment.start_date + (week_number - 1) * 7 hari.
     */
    public function getOpensAtForAssignment(TestProgramAssignment $assignment): Carbon
    {
        return $assignment->start_date->copy()->addDays(($this->week_number - 1) * 7);
    }

    /**
     * Max skor possible untuk minggu ini = jumlah skor tertinggi tiap soal.
     */
    public function getMaxScoreAttribute(): int
    {
        return $this->questions->sum(function (Question $question) {
            return $question->options->max('score') ?? 0;
        });
    }

    public function getQuestionsCountAttribute(): int
    {
        return $this->questions()->count();
    }
}
