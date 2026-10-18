<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soal dalam test per minggu. Tipe: radio/checkbox/likert/text.
     * Urutan via kolom order. is_required menentukan wajib dijawab.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_program_week_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // radio/checkbox/likert/text
            $table->text('label');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['test_program_week_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
