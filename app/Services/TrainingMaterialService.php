<?php

namespace App\Services;

use App\Enums\FileStatus;
use App\Enums\TrainingMaterialType;
use App\Jobs\ProcessTrainingMaterialUpload;
use App\Jobs\ProcessTrainingThumbnailUpload;
use App\Models\TrainingMaterial;
use App\Traits\HasDynamicLike;
use App\Traits\SortsQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TrainingMaterialService
{
    use HasDynamicLike, SortsQuery;

    public function getFiltered(
        ?string $search = null,
        ?string $typeFilter = null,
        ?string $statusFilter = null,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = TrainingMaterial::with(['creator']);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('title', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%")
                    ->orWhere('category', $operator, "%{$search}%");
            });
        }

        if ($typeFilter !== null && $typeFilter !== '') {
            $query->where('type', $typeFilter);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            if ($statusFilter === 'published') {
                $query->where('is_published', true);
            } elseif ($statusFilter === 'draft') {
                $query->where('is_published', false);
            } else {
                $query->where('file_status', $statusFilter);
            }
        }

        $sortable = [
            'title' => 'training_materials.title',
            'type' => 'CASE training_materials.type '
                .collect(TrainingMaterialType::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(TrainingMaterialType::cases()).' END',
            'file_status' => 'CASE training_materials.file_status '
                .collect(FileStatus::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(FileStatus::cases()).' END',
            'is_published' => 'training_materials.is_published',
            'created_at' => 'training_materials.created_at',
        ];

        $this->applySorting($query, $sortable, $sortField, $sortDir, 'created_at', 'desc', 'training_materials.id');

        return $query->paginate($perPage);
    }

    /**
     * Public library for users (only published + completed files).
     */
    /**
     * Get latest published materials for dashboard preview (no pagination).
     * Only returns materials with completed file status.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, TrainingMaterial>
     */
    public function getLatestPublished(int $limit = 3)
    {
        return TrainingMaterial::with(['creator'])
            ->published()
            ->completed()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getPublishedLibrary(
        ?string $search = null,
        ?string $typeFilter = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = TrainingMaterial::with(['creator'])
            ->published()
            ->completed();

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('title', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%")
                    ->orWhere('category', $operator, "%{$search}%");
            });
        }

        if ($typeFilter !== null && $typeFilter !== '') {
            $query->where('type', $typeFilter);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new training material with async file + thumbnail processing.
     *
     * @param  array  $data  title, description, type, category, is_published
     * @param  array|null  $filePayload  ['temp_path' => ..., 'original_name' => ...]
     * @param  array|null  $thumbnailPayload  ['temp_path' => ..., 'original_name' => ...]
     */
    public function create(array $data, ?array $filePayload = null, ?array $thumbnailPayload = null): TrainingMaterial
    {
        return DB::transaction(function () use ($data, $filePayload, $thumbnailPayload) {
            $material = TrainingMaterial::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'category' => $data['category'] ?? null,
                'file_status' => $filePayload ? FileStatus::Processing->value : null,
                'file_name' => $filePayload['original_name'] ?? null,
                'thumbnail_status' => $thumbnailPayload ? FileStatus::Processing->value : null,
                'thumbnail_name' => $thumbnailPayload['original_name'] ?? null,
                'is_published' => $data['is_published'] ?? false,
                'created_by' => auth()->id(),
            ]);

            if ($filePayload) {
                ProcessTrainingMaterialUpload::dispatch(
                    $material->id,
                    $filePayload['temp_path'],
                    $filePayload['original_name']
                );
            }

            if ($thumbnailPayload) {
                ProcessTrainingThumbnailUpload::dispatch(
                    $material->id,
                    $thumbnailPayload['temp_path'],
                    $thumbnailPayload['original_name']
                );
            }

            return $material;
        });
    }

    /**
     * Update training material metadata + optional file/thumbnail replacement.
     *
     * @param  array  $data  title, description, type, category, is_published
     * @param  array|null  $filePayload  new file to replace old one
     * @param  array|null  $thumbnailPayload  new thumbnail to replace old one
     */
    public function update(
        TrainingMaterial $material,
        array $data,
        ?array $filePayload = null,
        ?array $thumbnailPayload = null
    ): TrainingMaterial {
        return DB::transaction(function () use ($material, $data, $filePayload, $thumbnailPayload) {
            $material->update([
                'title' => $data['title'] ?? $material->title,
                'description' => $data['description'] ?? $material->description,
                'type' => $data['type'] ?? $material->type->value,
                'category' => $data['category'] ?? $material->category,
                'is_published' => $data['is_published'] ?? $material->is_published,
            ]);

            // Replace main file
            if ($filePayload) {
                app(FileStorageService::class)->delete($material->file_path);
                $material->update([
                    'file_status' => FileStatus::Processing,
                    'file_path' => null,
                    'file_name' => $filePayload['original_name'],
                    'file_size' => null,
                    'file_error' => null,
                    'file_processed_at' => null,
                ]);
                ProcessTrainingMaterialUpload::dispatch(
                    $material->id,
                    $filePayload['temp_path'],
                    $filePayload['original_name']
                );
            }

            // Replace thumbnail
            if ($thumbnailPayload) {
                app(FileStorageService::class)->delete($material->thumbnail_path);
                $material->update([
                    'thumbnail_status' => FileStatus::Processing,
                    'thumbnail_path' => null,
                    'thumbnail_name' => $thumbnailPayload['original_name'],
                    'thumbnail_size' => null,
                    'thumbnail_error' => null,
                    'thumbnail_processed_at' => null,
                ]);
                ProcessTrainingThumbnailUpload::dispatch(
                    $material->id,
                    $thumbnailPayload['temp_path'],
                    $thumbnailPayload['original_name']
                );
            }

            return $material->fresh();
        });
    }

    public function delete(TrainingMaterial $material): void
    {
        DB::transaction(function () use ($material) {
            $fileStorage = app(FileStorageService::class);
            $fileStorage->delete($material->file_path);
            $fileStorage->delete($material->thumbnail_path);
            $material->delete();
        });
    }

    public function togglePublish(TrainingMaterial $material): TrainingMaterial
    {
        $material->update(['is_published' => ! $material->is_published]);

        return $material->fresh();
    }

    /**
     * Mark file as completed (called by Job).
     */
    public function markFileCompleted(TrainingMaterial $material, string $filePath, string $fileName, int $fileSize): TrainingMaterial
    {
        $material->update([
            'file_status' => FileStatus::Completed,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_processed_at' => now(),
            'file_error' => null,
        ]);

        return $material->fresh();
    }

    /**
     * Mark file as failed (called by Job).
     */
    public function markFileFailed(TrainingMaterial $material, string $error): TrainingMaterial
    {
        $material->update([
            'file_status' => FileStatus::Failed,
            'file_error' => $error,
            'file_processed_at' => now(),
        ]);

        return $material->fresh();
    }

    /**
     * Mark thumbnail as completed (called by Job).
     */
    public function markThumbnailCompleted(TrainingMaterial $material, string $thumbPath, string $thumbName, int $thumbSize): TrainingMaterial
    {
        $material->update([
            'thumbnail_status' => FileStatus::Completed,
            'thumbnail_path' => $thumbPath,
            'thumbnail_name' => $thumbName,
            'thumbnail_size' => $thumbSize,
            'thumbnail_processed_at' => now(),
            'thumbnail_error' => null,
        ]);

        return $material->fresh();
    }

    /**
     * Mark thumbnail as failed (called by Job).
     */
    public function markThumbnailFailed(TrainingMaterial $material, string $error): TrainingMaterial
    {
        $material->update([
            'thumbnail_status' => FileStatus::Failed,
            'thumbnail_error' => $error,
            'thumbnail_processed_at' => now(),
        ]);

        return $material->fresh();
    }
}
