<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_program_week_id',
        'type',
        'label',
        'is_required',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'is_required' => 'boolean',
            'order' => 'integer',
        ];
    }

    // Relationships
    public function week(): BelongsTo
    {
        return $this->belongsTo(TestProgramWeek::class, 'test_program_week_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class);
    }

    // Helpers
    public function getIsScoredAttribute(): bool
    {
        return $this->type->isScored();
    }

    public function getHasOptionsAttribute(): bool
    {
        return $this->type->hasOptions();
    }

    /**
     * Skor maksimum untuk soal ini = score tertinggi dari semua opsi.
     */
    public function getMaxScoreAttribute(): int
    {
        if (! $this->hasOptions) {
            return 0;
        }

        return $this->options->max('score') ?? 0;
    }
}
