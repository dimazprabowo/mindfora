<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'question_option_id',
        'selected_option_ids',
        'answer_text',
    ];

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
        ];
    }

    // Relationships
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'test_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }

    /**
     * Hitung skor untuk jawaban ini berdasarkan tipe soal.
     */
    public function getCalculatedScoreAttribute(): int
    {
        $question = $this->question;
        if (! $question || ! $question->isScored) {
            return 0;
        }

        return match ($question->type) {
            \App\Enums\QuestionType::Radio, \App\Enums\QuestionType::Likert => $this->option?->score ?? 0,
            \App\Enums\QuestionType::Checkbox => QuestionOption::whereIn('id', $this->selected_option_ids ?? [])->sum('score'),
            default => 0,
        };
    }
}
