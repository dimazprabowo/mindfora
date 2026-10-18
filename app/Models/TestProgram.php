<?php

namespace App\Models;

use App\Enums\TestProgramStatus;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TestProgram extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TestProgramStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('test_program')
            ->logOnly(['name', 'description', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(TestProgramWeek::class)->orderBy('week_number');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TestProgramAssignment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', TestProgramStatus::Active);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', TestProgramStatus::Draft);
    }

    // Helpers
    public function getIsActiveAttribute(): bool
    {
        return $this->status === TestProgramStatus::Active;
    }

    public function getWeeksCountAttribute(): int
    {
        return $this->weeks()->count();
    }
}
