<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jawaban user per soal dalam attempt tertentu.
     * - radio/likert: question_option_id terisi (1 opsi)
     * - checkbox: selected_option_ids terisi (json array opsi ID)
     * - text: answer_text terisi
     */
    public function up(): void
    {
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->text('answer_text')->nullable();
            $table->timestamps();

            $table->index(['test_attempt_id', 'question_id']);
            $table->unique(['test_attempt_id', 'question_id']); // 1 jawaban per soal per attempt
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};
