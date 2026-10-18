<?php

namespace App\Jobs;

use App\Models\TrainingMaterial;
use App\Services\FileStorageService;
use App\Services\TrainingMaterialService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessTrainingThumbnailUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $materialId,
        public string $tempPath,
        public string $originalName,
    ) {}

    public function handle(FileStorageService $fileStorage, TrainingMaterialService $service): void
    {
        $material = TrainingMaterial::find($this->materialId);

        if (! $material) {
            return;
        }

        if (! Storage::disk('local')->exists($this->tempPath)) {
            $service->markThumbnailFailed($material, 'File sementara thumbnail tidak ditemukan.');

            return;
        }

        try {
            $result = $fileStorage->moveFromTemp(
                $this->tempPath,
                $this->originalName,
                'training-thumbnails',
                [str()->slug($material->title)]
            );

            $service->markThumbnailCompleted($material, $result['path'], $result['name'], $result['size']);
        } catch (\Exception $e) {
            $service->markThumbnailFailed($material, $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $material = TrainingMaterial::find($this->materialId);
        if ($material && $material->thumbnail_status?->value === 'processing') {
            app(TrainingMaterialService::class)->markThumbnailFailed($material, $exception->getMessage());
        }
    }
}
