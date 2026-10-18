<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TestProgramAssignment extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'test_program_id',
        'label',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => AssignmentStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('test_assignment')
            ->logOnly(['test_program_id', 'label', 'start_date', 'end_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function program(): BelongsTo
    {
        return $this->belongsTo(TestProgram::class, 'test_program_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_program_assignment_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', AssignmentStatus::Active);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', AssignmentStatus::Scheduled);
    }

    // Helpers
    public function getIsActiveAttribute(): bool
    {
        return $this->status === AssignmentStatus::Active;
    }

    /**
     * Cek apakah tanggal saat ini sudah masuk ke minggu-N dari assignment ini.
     */
    public function isWeekOpenByCalendar(int $weekNumber): bool
    {
        $weekOpensAt = $this->start_date->copy()->addDays(($weekNumber - 1) * 7);

        return now()->startOfDay()->gte($weekOpensAt);
    }

    /**
     * Ambil minggu yang sedang aktif berdasarkan kalender (week number 1-4).
     * Return null kalau belum mulai atau sudah selesai 4 minggu.
     */
    public function getCurrentWeekNumber(): ?int
    {
        $daysSinceStart = $this->start_date->startOfDay()->diffInDays(now()->startOfDay());

        if ($daysSinceStart < 0) {
            return null; // belum mulai
        }

        $weekNumber = (int) floor($daysSinceStart / 7) + 1;

        return $weekNumber > 4 ? null : $weekNumber;
    }
}
