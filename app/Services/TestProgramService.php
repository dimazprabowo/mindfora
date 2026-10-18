<?php

namespace App\Services;

use App\Enums\TestProgramStatus;
use App\Models\TestProgram;
use App\Models\User;
use App\Traits\HasDynamicLike;
use App\Traits\SortsQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TestProgramService
{
    use HasDynamicLike, SortsQuery;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = TestProgram::with(['weeks' => fn ($q) => $q->orderBy('week_number'), 'creator'])
            ->withCount('weeks');

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('name', $operator, "%{$search}%")
                    ->orWhere('description', $operator, "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $sortable = [
            'name' => 'test_programs.name',
            'weeks_count' => '(SELECT COUNT(*) FROM test_program_weeks WHERE test_program_weeks.test_program_id = test_programs.id)',
            'creator' => '('.User::select('name')
                ->whereColumn('users.id', 'test_programs.created_by')->limit(1)->toSql().')',
            'status' => 'CASE test_programs.status '
                .collect(TestProgramStatus::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(TestProgramStatus::cases()).' END',
            'created_at' => 'test_programs.created_at',
        ];

        $this->applySorting($query, $sortable, $sortField, $sortDir, 'created_at', 'desc', 'test_programs.id');

        return $query->paginate($perPage);
    }

    public function create(array $data): TestProgram
    {
        return DB::transaction(function () use ($data) {
            $program = TestProgram::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? TestProgramStatus::Draft->value,
                'created_by' => auth()->id(),
            ]);

            $this->syncWeeks($program, $data['weeks'] ?? []);

            return $program->fresh(['weeks.questions.options']);
        });
    }

    public function update(TestProgram $program, array $data): TestProgram
    {
        return DB::transaction(function () use ($program, $data) {
            $program->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? $program->status->value,
            ]);

            $this->syncWeeks($program, $data['weeks'] ?? []);

            return $program->fresh(['weeks.questions.options']);
        });
    }

    public function delete(TestProgram $program): void
    {
        $program->delete();
    }

    public function publish(TestProgram $program): TestProgram
    {
        $program->update(['status' => TestProgramStatus::Active]);

        return $program->fresh();
    }

    public function archive(TestProgram $program): TestProgram
    {
        $program->update(['status' => TestProgramStatus::Archived]);

        return $program->fresh();
    }

    /**
     * Sync weeks + nested questions + options.
     * Delete removed weeks/questions/options, update existing, create new.
     */
    protected function syncWeeks(TestProgram $program, array $weeks): void
    {
        $existingWeekIds = $program->weeks()->pluck('id')->toArray();
        $keepWeekIds = [];

        foreach ($weeks as $weekData) {
            $weekId = $weekData['id'] ?? null;

            if ($weekId && in_array($weekId, $existingWeekIds)) {
                $program->weeks()->where('id', $weekId)->update([
                    'week_number' => $weekData['week_number'],
                    'title' => $weekData['title'] ?? null,
                    'is_active' => $weekData['is_active'] ?? true,
                    'threshold_low_max' => $weekData['threshold_low_max'] ?? null,
                    'threshold_medium_max' => $weekData['threshold_medium_max'] ?? null,
                ]);
                $week = $program->weeks()->find($weekId);
                $keepWeekIds[] = $weekId;
            } else {
                $week = $program->weeks()->create([
                    'week_number' => $weekData['week_number'],
                    'title' => $weekData['title'] ?? null,
                    'is_active' => $weekData['is_active'] ?? true,
                    'threshold_low_max' => $weekData['threshold_low_max'] ?? null,
                    'threshold_medium_max' => $weekData['threshold_medium_max'] ?? null,
                ]);
                $keepWeekIds[] = $week->id;
            }

            $this->syncQuestions($week, $weekData['questions'] ?? []);
        }

        // Delete removed weeks (cascade will delete questions + options)
        $deleteWeekIds = array_diff($existingWeekIds, $keepWeekIds);
        if (! empty($deleteWeekIds)) {
            $program->weeks()->whereIn('id', $deleteWeekIds)->delete();
        }
    }

    protected function syncQuestions($week, array $questions): void
    {
        $existingQuestionIds = $week->questions()->pluck('id')->toArray();
        $keepQuestionIds = [];

        foreach ($questions as $questionData) {
            $questionId = $questionData['id'] ?? null;

            if ($questionId && in_array($questionId, $existingQuestionIds)) {
                $week->questions()->where('id', $questionId)->update([
                    'type' => $questionData['type'],
                    'label' => $questionData['label'],
                    'is_required' => $questionData['is_required'] ?? true,
                    'order' => $questionData['order'] ?? 0,
                ]);
                $question = $week->questions()->find($questionId);
                $keepQuestionIds[] = $questionId;
            } else {
                $question = $week->questions()->create([
                    'type' => $questionData['type'],
                    'label' => $questionData['label'],
                    'is_required' => $questionData['is_required'] ?? true,
                    'order' => $questionData['order'] ?? 0,
                ]);
                $keepQuestionIds[] = $question->id;
            }

            // Only sync options for scored question types
            if (in_array($questionData['type'], ['radio', 'checkbox', 'likert'])) {
                $this->syncOptions($question, $questionData['options'] ?? []);
            } else {
                // Text questions don't have options — delete any existing
                $question->options()->delete();
            }
        }

        $deleteQuestionIds = array_diff($existingQuestionIds, $keepQuestionIds);
        if (! empty($deleteQuestionIds)) {
            $week->questions()->whereIn('id', $deleteQuestionIds)->delete();
        }
    }

    protected function syncOptions($question, array $options): void
    {
        $existingOptionIds = $question->options()->pluck('id')->toArray();
        $keepOptionIds = [];

        foreach ($options as $optionData) {
            $optionId = $optionData['id'] ?? null;

            if ($optionId && in_array($optionId, $existingOptionIds)) {
                $question->options()->where('id', $optionId)->update([
                    'label' => $optionData['label'],
                    'score' => $optionData['score'] ?? 0,
                    'order' => $optionData['order'] ?? 0,
                ]);
                $keepOptionIds[] = $optionId;
            } else {
                $option = $question->options()->create([
                    'label' => $optionData['label'],
                    'score' => $optionData['score'] ?? 0,
                    'order' => $optionData['order'] ?? 0,
                ]);
                $keepOptionIds[] = $option->id;
            }
        }

        $deleteOptionIds = array_diff($existingOptionIds, $keepOptionIds);
        if (! empty($deleteOptionIds)) {
            $question->options()->whereIn('id', $deleteOptionIds)->delete();
        }
    }

    public function findWithRelations(int $id): TestProgram
    {
        return TestProgram::with(['weeks.questions.options' => fn ($q) => $q->orderBy('order'), 'weeks' => fn ($q) => $q->orderBy('week_number')])
            ->findOrFail($id);
    }
}
