<?php

namespace App\Services;

use App\Enums\AttemptStatus;
use App\Enums\StressCategory;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\TestProgramAssignment;
use App\Models\TestProgramWeek;
use App\Models\User;
use App\Traits\HasDynamicLike;
use App\Traits\SortsQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TestAttemptService
{
    use HasDynamicLike, SortsQuery;

    /**
     * Get the active assignment for a user (only one active at a time).
     */
    public function getActiveAssignmentForUser(User $user): ?TestProgramAssignment
    {
        return TestProgramAssignment::with(['program.weeks.questions.options' => fn ($q) => $q->orderBy('order')])
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
    }

    /**
     * Get week status for user dashboard (4 weeks).
     * Returns array of week status with unlock info, attempt info, best score.
     */
    public function getWeekStatusesForUser(User $user, TestProgramAssignment $assignment): array
    {
        $currentWeek = $assignment->getCurrentWeekNumber();
        $statuses = [];

        foreach ($assignment->program->weeks->sortBy('week_number') as $week) {
            $weekOpensAt = $week->getOpensAtForAssignment($assignment);

            // Determine unlock status
            if ($currentWeek === null) {
                $unlockStatus = 'locked'; // program ended or not started
                $unlockLabel = $assignment->start_date->isFuture() ? 'Belum dimulai' : 'Program selesai';
            } elseif ($week->week_number < $currentWeek) {
                $unlockStatus = 'open';
                $unlockLabel = 'Dapat dikerjakan ulang';
            } elseif ($week->week_number === $currentWeek) {
                $unlockStatus = 'open';
                $unlockLabel = 'Minggu ini';
            } else {
                $unlockStatus = 'locked';
                $unlockLabel = 'Buka '.$weekOpensAt->format('d M Y');
            }

            // Get user's attempts for this week
            $attempts = $week->attempts()
                ->where('user_id', $user->id)
                ->where('test_program_assignment_id', $assignment->id)
                ->orderByDesc('submitted_at')
                ->get();

            $bestAttempt = $attempts->where('is_best', true)->first() ?? $attempts->where('status', AttemptStatus::Submitted)->sortByDesc('score_total')->first();
            $inProgress = $attempts->where('status', AttemptStatus::InProgress)->first();

            $statuses[] = [
                'week' => $week,
                'unlock_status' => $unlockStatus,
                'unlock_label' => $unlockLabel,
                'opens_at' => $weekOpensAt,
                'is_current' => $currentWeek === $week->week_number,
                'attempts_count' => $attempts->where('status', AttemptStatus::Submitted)->count(),
                'best_score' => $bestAttempt?->score_total,
                'best_category' => $bestAttempt?->score_category,
                'has_in_progress' => $inProgress !== null,
                'in_progress_id' => $inProgress?->id,
            ];
        }

        return $statuses;
    }

    /**
     * Start a new attempt or resume an in-progress one.
     */
    public function startOrResumeAttempt(User $user, TestProgramAssignment $assignment, TestProgramWeek $week): TestAttempt
    {
        // Check for existing in-progress attempt
        $existing = TestAttempt::where('user_id', $user->id)
            ->where('test_program_assignment_id', $assignment->id)
            ->where('test_program_week_id', $week->id)
            ->where('status', AttemptStatus::InProgress)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $assignment, $week) {
            return TestAttempt::create([
                'test_program_assignment_id' => $assignment->id,
                'test_program_week_id' => $week->id,
                'user_id' => $user->id,
                'status' => AttemptStatus::InProgress,
                'started_at' => now(),
            ]);
        });
    }

    /**
     * Save partial answers (autosave / resume later).
     */
    public function saveAnswers(TestAttempt $attempt, array $answers): void
    {
        DB::transaction(function () use ($attempt, $answers) {
            foreach ($answers as $questionId => $answerData) {
                $this->upsertAnswer($attempt, (int) $questionId, $answerData);
            }
        });
    }

    /**
     * Submit attempt: validate required questions, calculate score, mark best.
     */
    public function submitAttempt(TestAttempt $attempt, array $answers): TestAttempt
    {
        return DB::transaction(function () use ($attempt, $answers) {
            // Save all answers first
            foreach ($answers as $questionId => $answerData) {
                $this->upsertAnswer($attempt, (int) $questionId, $answerData);
            }

            // Calculate score
            $week = $attempt->week->fresh(['questions.options']);
            $scoreTotal = 0;
            $maxPossible = 0;

            foreach ($week->questions as $question) {
                $maxPossible += $question->max_score;

                $answer = $attempt->answers()->where('question_id', $question->id)->first();
                if (! $answer) {
                    continue;
                }
                $scoreTotal += $answer->calculated_score;
            }

            // Determine stress category
            $category = StressCategory::fromScore(
                $scoreTotal,
                $maxPossible,
                $week->threshold_low_max,
                $week->threshold_medium_max
            );

            $attempt->update([
                'status' => AttemptStatus::Submitted,
                'score_total' => $scoreTotal,
                'score_category' => $category,
                'submitted_at' => now(),
            ]);

            // Re-evaluate best score for this user/week/assignment
            $this->markBestAttempt($attempt->user_id, $attempt->test_program_assignment_id, $attempt->test_program_week_id);

            return $attempt->fresh();
        });
    }

    /**
     * Re-evaluate which attempt is the "best" (highest score) for a given user/week/assignment.
     * Ties: latest submitted wins.
     */
    protected function markBestAttempt(int $userId, int $assignmentId, int $weekId): void
    {
        // Clear all is_best for this user/week/assignment
        TestAttempt::where('user_id', $userId)
            ->where('test_program_assignment_id', $assignmentId)
            ->where('test_program_week_id', $weekId)
            ->update(['is_best' => false]);

        // Find the best (max score, latest submitted_at as tiebreaker)
        $best = TestAttempt::where('user_id', $userId)
            ->where('test_program_assignment_id', $assignmentId)
            ->where('test_program_week_id', $weekId)
            ->where('status', AttemptStatus::Submitted)
            ->orderByDesc('score_total')
            ->orderByDesc('submitted_at')
            ->first();

        if ($best) {
            $best->update(['is_best' => true]);
        }
    }

    /**
     * Upsert a single answer for an attempt.
     */
    protected function upsertAnswer(TestAttempt $attempt, int $questionId, array $answerData): void
    {
        $answer = TestAnswer::where('test_attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->first();

        $payload = [
            'question_option_id' => $answerData['option_id'] ?? null,
            'selected_option_ids' => $answerData['option_ids'] ?? null,
            'answer_text' => $answerData['text'] ?? null,
        ];

        if ($answer) {
            $answer->update($payload);
        } else {
            $payload['test_attempt_id'] = $attempt->id;
            $payload['question_id'] = $questionId;
            TestAnswer::create($payload);
        }
    }

    /**
     * Get user's submitted attempts for history.
     */
    public function getUserHistory(User $user): Collection
    {
        return TestAttempt::with(['week.program', 'assignment'])
            ->where('user_id', $user->id)
            ->where('status', AttemptStatus::Submitted)
            ->orderByDesc('submitted_at')
            ->get();
    }

    /**
     * Get all attempts for admin view.
     */
    public function getFilteredAttempts(
        ?string $search = null,
        ?string $categoryFilter = null,
        ?int $weekFilter = null,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = TestAttempt::with(['user', 'week.program', 'assignment'])
            ->where('status', AttemptStatus::Submitted);

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->whereHas('user', fn ($uq) => $uq->where('name', $operator, "%{$search}%")->orWhere('email', $operator, "%{$search}%"))
                    ->orWhereHas('week.program', fn ($pq) => $pq->where('name', $operator, "%{$search}%"));
            });
        }

        if ($categoryFilter !== null && $categoryFilter !== '') {
            $query->where('score_category', $categoryFilter);
        }

        if ($weekFilter) {
            $query->whereHas('week', fn ($wq) => $wq->where('week_number', $weekFilter));
        }

        $sortable = [
            'user' => '('.User::select('name')
                ->whereColumn('users.id', 'test_attempts.user_id')->limit(1)->toSql().')',
            'program' => '(SELECT test_programs.name FROM test_program_weeks '
                .'JOIN test_programs ON test_programs.id = test_program_weeks.test_program_id '
                .'WHERE test_program_weeks.id = test_attempts.test_program_week_id LIMIT 1)',
            'week' => '(SELECT week_number FROM test_program_weeks '
                .'WHERE test_program_weeks.id = test_attempts.test_program_week_id LIMIT 1)',
            'score_total' => 'test_attempts.score_total',
            'score_category' => 'CASE test_attempts.score_category '
                .collect(StressCategory::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(StressCategory::cases()).' END',
            'is_best' => 'test_attempts.is_best',
            'submitted_at' => 'test_attempts.submitted_at',
        ];

        $this->applySorting($query, $sortable, $sortField, $sortDir, 'submitted_at', 'desc', 'test_attempts.id');

        return $query->paginate($perPage);
    }
}
