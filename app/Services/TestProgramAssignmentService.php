<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Models\TestProgram;
use App\Models\TestProgramAssignment;
use App\Traits\HasDynamicLike;
use App\Traits\SortsQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TestProgramAssignmentService
{
    use HasDynamicLike, SortsQuery;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        ?int $programFilter = null,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = TestProgramAssignment::with(['program', 'creator'])
            ->withCount('attempts');

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('label', $operator, "%{$search}%")
                    ->orWhereHas('program', fn ($pq) => $pq->where('name', $operator, "%{$search}%"));
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        if ($programFilter) {
            $query->where('test_program_id', $programFilter);
        }

        $sortable = [
            'program' => '('.TestProgram::select('name')
                ->whereColumn('test_programs.id', 'test_program_assignments.test_program_id')->limit(1)->toSql().')',
            'label' => 'test_program_assignments.label',
            'start_date' => 'test_program_assignments.start_date',
            'end_date' => 'test_program_assignments.end_date',
            'attempts_count' => '(SELECT COUNT(*) FROM test_attempts WHERE test_attempts.test_program_assignment_id = test_program_assignments.id)',
            'status' => 'CASE test_program_assignments.status '
                .collect(AssignmentStatus::cases())->map(fn ($s, $i) => "WHEN '{$s->value}' THEN {$i}")->implode(' ')
                .' ELSE '.count(AssignmentStatus::cases()).' END',
        ];

        $this->applySorting($query, $sortable, $sortField, $sortDir, 'start_date', 'desc', 'test_program_assignments.id');

        return $query->paginate($perPage);
    }

    public function create(array $data): TestProgramAssignment
    {
        return DB::transaction(function () use ($data) {
            return TestProgramAssignment::create([
                'test_program_id' => $data['test_program_id'],
                'label' => $data['label'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'] ?? AssignmentStatus::Scheduled->value,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function update(TestProgramAssignment $assignment, array $data): TestProgramAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $assignment->update([
                'test_program_id' => $data['test_program_id'] ?? $assignment->test_program_id,
                'label' => $data['label'] ?? $assignment->label,
                'start_date' => $data['start_date'] ?? $assignment->start_date,
                'end_date' => $data['end_date'] ?? $assignment->end_date,
                'status' => $data['status'] ?? $assignment->status->value,
            ]);

            return $assignment->fresh();
        });
    }

    public function delete(TestProgramAssignment $assignment): void
    {
        $assignment->delete();
    }

    public function activate(TestProgramAssignment $assignment): TestProgramAssignment
    {
        // Only one active assignment at a time — set others to completed
        DB::transaction(function () use ($assignment) {
            TestProgramAssignment::where('id', '!=', $assignment->id)
                ->where('status', AssignmentStatus::Active)
                ->update(['status' => AssignmentStatus::Completed->value]);

            $assignment->update(['status' => AssignmentStatus::Active]);
        });

        return $assignment->fresh();
    }

    public function cancel(TestProgramAssignment $assignment): TestProgramAssignment
    {
        $assignment->update(['status' => AssignmentStatus::Cancelled]);

        return $assignment->fresh();
    }

    public function complete(TestProgramAssignment $assignment): TestProgramAssignment
    {
        $assignment->update(['status' => AssignmentStatus::Completed]);

        return $assignment->fresh();
    }

    /**
     * Get the currently active assignment (only one allowed).
     */
    public function getActiveAssignment(): ?TestProgramAssignment
    {
        return TestProgramAssignment::with(['program.weeks.questions.options'])
            ->where('status', AssignmentStatus::Active)
            ->latest('start_date')
            ->first();
    }

    /**
     * Get active program options for select dropdown.
     */
    public function getProgramOptions(): array
    {
        return TestProgram::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => ['value' => $p->id, 'label' => $p->name])
            ->toArray();
    }
}
