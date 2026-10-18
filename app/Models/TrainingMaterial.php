<?php

namespace App\Models;

use App\Enums\FileStatus;
use App\Enums\TrainingMaterialType;
use App\Traits\HasEncryptedRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TrainingMaterial extends Model
{
    use HasEncryptedRouteKey, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'file_status',
        'file_path',
        'file_name',
        'file_size',
        'file_error',
        'file_processed_at',
        'thumbnail_path',
        'thumbnail_name',
        'thumbnail_size',
        'thumbnail_status',
        'thumbnail_error',
        'thumbnail_processed_at',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => TrainingMaterialType::class,
            'file_status' => FileStatus::class,
            'file_size' => 'integer',
            'file_processed_at' => 'datetime',
            'thumbnail_status' => FileStatus::class,
            'thumbnail_size' => 'integer',
            'thumbnail_processed_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('training_material')
            ->logOnly(['title', 'description', 'type', 'category', 'file_status', 'thumbnail_status', 'is_published'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeAudio($query)
    {
        return $query->where('type', TrainingMaterialType::Audio);
    }

    public function scopeVideo($query)
    {
        return $query->where('type', TrainingMaterialType::Video);
    }

    public function scopeCompleted($query)
    {
        return $query->where('file_status', FileStatus::Completed);
    }

    // Helpers
    public function getIsPublishedAttribute(): bool
    {
        return (bool) $this->attributes['is_published'];
    }

    public function getIsReadyAttribute(): bool
    {
        return $this->file_status === FileStatus::Completed && $this->file_path;
    }

    public function getIsThumbnailReadyAttribute(): bool
    {
        return $this->thumbnail_status === FileStatus::Completed && $this->thumbnail_path;
    }

    public function getHasThumbnailAttribute(): bool
    {
        return $this->thumbnail_path !== null;
    }

    public function getFileSizeFormattedAttribute(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
