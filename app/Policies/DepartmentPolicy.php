<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('departments_view');
    }

    public function create(User $user): bool
    {
        return $user->can('departments_create');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can('departments_update');
    }

    public function delete(User $user, Department $department): bool
    {
        if ($department->users()->exists()) {
            return false;
        }

        return $user->can('departments_delete');
    }

    public function toggleStatus(User $user, Department $department): bool
    {
        return $user->can('departments_update');
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('departments_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('departments_export_pdf');
    }
}
