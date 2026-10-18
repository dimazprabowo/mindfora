<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attempt = pengerjaan test oleh user untuk minggu tertentu dalam assignment tertentu.
     * Boleh retake — simpan semua attempt. is_best = flag skor terbaik untuk minggu itu.
     * status: in_progress (simpan parsial/resume) / submitted (final).
     */
    public function up(): void
    {
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_program_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_program_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('in_progress'); // in_progress/submitted
            $table->integer('score_total')->nullable(); // null saat in_progress, diisi saat submit
            $table->string('score_category', 10)->nullable(); // rendah/sedang/tinggi
            $table->boolean('is_best')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['test_program_assignment_id', 'user_id']);
            $table->index(['test_program_week_id', 'user_id']);
            $table->index(['user_id', 'is_best']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};
