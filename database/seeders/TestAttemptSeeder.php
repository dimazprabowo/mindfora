<?php

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Enums\AttemptStatus;
use App\Enums\QuestionType;
use App\Enums\StressCategory;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestProgram;
use App\Models\TestProgramAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TestAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@app.com')->first();
        if (! $user) {
            return;
        }

        $dassProgram = TestProgram::where('name', 'DASS-42 Stress Assessment')->first();
        if (! $dassProgram) {
            return;
        }

        // Ambil assignment aktif (Batch 1, mulai 7 hari lalu)
        $assignment = TestProgramAssignment::where('test_program_id', $dassProgram->id)
            ->where('status', AssignmentStatus::Active)
            ->first();
        if (! $assignment) {
            return;
        }

        // Ambil minggu 1
        $week1 = $dassProgram->weeks()->where('week_number', 1)->first();
        if (! $week1) {
            return;
        }

        // Cek apakah sudah ada attempt untuk user ini di minggu 1 (idempotent)
        if (TestAttempt::where('user_id', $user->id)->where('test_program_week_id', $week1->id)->exists()) {
            return;
        }

        // ============================================================
        // Attempt 1: Minggu 1 — skor sedang (jawaban random 1-2)
        // ============================================================
        $attempt1 = TestAttempt::create([
            'test_program_assignment_id' => $assignment->id,
            'test_program_week_id' => $week1->id,
            'user_id' => $user->id,
            'status' => AttemptStatus::Submitted,
            'score_total' => 0, // akan dihitung di bawah
            'score_category' => null, // akan dihitung di bawah
            'is_best' => true,
            'started_at' => Carbon::today()->subDays(6)->setHour(9)->setMinute(0),
            'submitted_at' => Carbon::today()->subDays(6)->setHour(9)->setMinute(15),
        ]);

        $scoreTotal = 0;
        $questions = $week1->questions()->orderBy('order')->get();

        foreach ($questions as $index => $question) {
            if ($question->type === QuestionType::Likert) {
                // Pilih opsi dengan skor 1 atau 2 (stres sedang)
                $targetScore = ($index % 3 === 0) ? 2 : 1;
                $option = $question->options()->where('score', $targetScore)->first();

                if ($option) {
                    TestAnswer::create([
                        'test_attempt_id' => $attempt1->id,
                        'question_id' => $question->id,
                        'question_option_id' => $option->id,
                        'selected_option_ids' => null,
                        'answer_text' => null,
                    ]);
                    $scoreTotal += $option->score;
                }
            }
        }

        // Hitung kategori stres berdasarkan threshold minggu 1
        $maxScore = $week1->max_score;
        $category = StressCategory::fromScore(
            $scoreTotal,
            $maxScore,
            $week1->threshold_low_max,
            $week1->threshold_medium_max
        );

        $attempt1->update([
            'score_total' => $scoreTotal,
            'score_category' => $category,
        ]);

        // ============================================================
        // Attempt 2 (retake): Minggu 1 — skor lebih rendah (lebih baik)
        // ============================================================
        $attempt2 = TestAttempt::create([
            'test_program_assignment_id' => $assignment->id,
            'test_program_week_id' => $week1->id,
            'user_id' => $user->id,
            'status' => AttemptStatus::Submitted,
            'score_total' => 0,
            'score_category' => null,
            'is_best' => false, // attempt1 tetap best (skor lebih tinggi = lebih stres, tapi is_best = skor tertinggi)
            'started_at' => Carbon::today()->subDays(5)->setHour(10)->setMinute(0),
            'submitted_at' => Carbon::today()->subDays(5)->setHour(10)->setMinute(12),
        ]);

        $scoreTotal2 = 0;
        foreach ($questions as $index => $question) {
            if ($question->type === QuestionType::Likert) {
                // Pilih opsi dengan skor 0 atau 1 (stres rendah)
                $targetScore = ($index % 4 === 0) ? 1 : 0;
                $option = $question->options()->where('score', $targetScore)->first();

                if ($option) {
                    TestAnswer::create([
                        'test_attempt_id' => $attempt2->id,
                        'question_id' => $question->id,
                        'question_option_id' => $option->id,
                        'selected_option_ids' => null,
                        'answer_text' => null,
                    ]);
                    $scoreTotal2 += $option->score;
                }
            }
        }

        $category2 = StressCategory::fromScore(
            $scoreTotal2,
            $maxScore,
            $week1->threshold_low_max,
            $week1->threshold_medium_max
        );

        $attempt2->update([
            'score_total' => $scoreTotal2,
            'score_category' => $category2,
        ]);

        // ============================================================
        // Attempt 3: Minggu 2 — in_progress (belum submit, autosave)
        // ============================================================
        $week2 = $dassProgram->weeks()->where('week_number', 2)->first();
        if ($week2) {
            $attempt3 = TestAttempt::create([
                'test_program_assignment_id' => $assignment->id,
                'test_program_week_id' => $week2->id,
                'user_id' => $user->id,
                'status' => AttemptStatus::InProgress,
                'score_total' => null,
                'score_category' => null,
                'is_best' => false,
                'started_at' => Carbon::today()->setHour(8)->setMinute(30),
                'submitted_at' => null,
            ]);

            // Simpan 3 jawaban parsial (autosave demo)
            $week2Questions = $week2->questions()->orderBy('order')->limit(3)->get();
            foreach ($week2Questions as $question) {
                if ($question->type === QuestionType::Likert) {
                    $option = $question->options()->where('score', 1)->first();
                    if ($option) {
                        TestAnswer::create([
                            'test_attempt_id' => $attempt3->id,
                            'question_id' => $question->id,
                            'question_option_id' => $option->id,
                            'selected_option_ids' => null,
                            'answer_text' => null,
                        ]);
                    }
                }
            }
        }
    }
}
