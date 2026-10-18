<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assignment = penjadwalan program ke periode (bulan).
     * Contoh: Januari 2026 = Program A, Februari 2026 = Program B.
     * Hanya 1 assignment berstatus 'active' pada satu waktu.
     * start_date global untuk semua peserta (enrollment otomatis semua user aktif).
     */
    public function up(): void
    {
        Schema::create('test_program_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_program_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // mis. "Januari 2026"
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled/active/completed/cancelled
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('start_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_program_assignments');
    }
};
