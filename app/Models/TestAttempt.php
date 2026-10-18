<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use App\Enums\StressCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TestAttempt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'test_program_assignment_id',
        'test_program_week_id',
        'user_id',
        'status',
        'score_total',
        'score_category',
        'is_best',
        'started_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AttemptStatus::class,
            'score_category' => StressCategory::class,
            'is_best' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('test_attempt')
            ->logOnly(['status', 'score_total', 'score_category', 'is_best', 'submitted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TestProgramAssignment::class, 'test_program_assignment_id');
    }

    public function week(): BelongsTo
    {
        return $this->belongsTo(TestProgramWeek::class, 'test_program_week_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class, 'test_attempt_id');
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', AttemptStatus::Submitted);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', AttemptStatus::InProgress);
    }

    public function scopeBest($query)
    {
        return $query->where('is_best', true);
    }

    public function scopeForUser($query, ?int $userId)
    {
        return $query->where('user_id', $userId ?? 0);
    }

    // Helpers
    public function getIsSubmittedAttribute(): bool
    {
        return $this->status === AttemptStatus::Submitted;
    }

    public function getScoreCategoryLabelAttribute(): ?string
    {
        return $this->score_category?->label();
    }
}
