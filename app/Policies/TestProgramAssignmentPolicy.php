<?php

namespace App\Policies;

use App\Models\TestProgramAssignment;
use App\Models\User;

class TestProgramAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('test_assignments_view');
    }

    public function view(User $user, TestProgramAssignment $assignment): bool
    {
        return $user->can('test_assignments_view');
    }

    public function create(User $user): bool
    {
        return $user->can('test_assignments_create');
    }

    public function update(User $user, TestProgramAssignment $assignment): bool
    {
        return $user->can('test_assignments_update');
    }

    public function delete(User $user, TestProgramAssignment $assignment): bool
    {
        // Tidak bisa hapus jika sudah ada attempt yang disubmit
        if ($assignment->attempts()->where('status', 'submitted')->exists()) {
            return false;
        }

        return $user->can('test_assignments_delete');
    }
}
