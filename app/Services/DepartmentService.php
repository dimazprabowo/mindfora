<?php

namespace App\Services;

use App\Models\Department;
use App\Traits\HasDynamicLike;
use App\Traits\SortsQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    use HasDynamicLike, SortsQuery;

    public function getFiltered(
        ?string $search = null,
        ?string $statusFilter = null,
        int $perPage = 15,
        ?string $sortField = null,
        string $sortDir = 'asc'
    ): LengthAwarePaginator {
        $query = Department::withCount('users');

        if ($search) {
            $operator = $this->getLikeOperator();
            $query->where(function ($q) use ($search, $operator) {
                $q->where('code', $operator, "%{$search}%")
                    ->orWhere('name', $operator, "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', (bool) $statusFilter);
        }

        $sortable = [
            'name' => 'departments.name',
            'code' => 'departments.code',
            'users_count' => '(SELECT COUNT(*) FROM users WHERE users.department_id = departments.id)',
            'is_active' => 'departments.is_active',
        ];

        $this->applySorting($query, $sortable, $sortField, $sortDir, 'name', 'asc', 'departments.id');

        return $query->paginate($perPage);
    }

    public function create(array $data): Department
    {
        return Department::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? $department->is_active,
        ]);

        return $department;
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    public function toggleStatus(Department $department): Department
    {
        $department->update(['is_active' => ! $department->is_active]);

        return $department->fresh();
    }

    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return Department::active()->orderBy('name')->get();
    }
}
