<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Materi pelatihan (audio terapi & video mindfulness).
     * Upload async via Job — file_status & thumbnail_status: processing/completed/failed.
     */
    public function up(): void
    {
        Schema::create('training_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 10); // audio/video
            $table->string('category')->nullable();

            // File utama (audio/video) — async via worker
            $table->string('file_status', 15)->default('processing'); // processing/completed/failed
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_error')->nullable();
            $table->timestamp('file_processed_at')->nullable();

            // Thumbnail (cover image) — async via worker, opsional
            $table->string('thumbnail_path')->nullable();
            $table->string('thumbnail_name')->nullable();
            $table->unsignedBigInteger('thumbnail_size')->nullable();
            $table->string('thumbnail_status', 15)->nullable(); // null=no thumbnail, processing/completed/failed
            $table->string('thumbnail_error')->nullable();
            $table->timestamp('thumbnail_processed_at')->nullable();

            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_published']);
            $table->index('category');
            $table->index('file_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_materials');
    }
};
