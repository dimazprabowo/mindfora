<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Minggu dalam program test (1-4). Tiap minggu = 1 test.
     * opens_at tidak disimpan — computed dari assignment.start_date + (week_number-1)*7.
     * Threshold skor nullable — kalau null, pakai default persentase (0-33%/34-66%/67-100%).
     */
    public function up(): void
    {
        Schema::create('test_program_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week_number'); // 1-4
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('threshold_low_max')->nullable();
            $table->unsignedInteger('threshold_medium_max')->nullable();
            $table->timestamps();

            $table->unique(['test_program_id', 'week_number']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_program_weeks');
    }
};
