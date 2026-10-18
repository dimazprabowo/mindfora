<?php

namespace App\Policies;

use App\Models\TestProgram;
use App\Models\User;

class TestProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('test_programs_view');
    }

    public function view(User $user, TestProgram $program): bool
    {
        return $user->can('test_programs_view');
    }

    public function create(User $user): bool
    {
        return $user->can('test_programs_create');
    }

    public function update(User $user, TestProgram $program): bool
    {
        return $user->can('test_programs_update');
    }

    public function delete(User $user, TestProgram $program): bool
    {
        // Tidak bisa hapus jika masih punya assignment aktif
        if ($program->assignments()->where('status', 'active')->exists()) {
            return false;
        }

        return $user->can('test_programs_delete');
    }

    public function publish(User $user, TestProgram $program): bool
    {
        return $user->can('test_programs_publish');
    }

    public function exportExcel(User $user): bool
    {
        return $user->can('test_programs_export_excel');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('test_programs_export_pdf');
    }
}
