<?php

namespace App\Policies;

use App\Models\TestAttempt;
use App\Models\TestProgramWeek;
use App\Models\User;

class TestAttemptPolicy
{
    /**
     * Admin/super admin — lihat hasil semua peserta.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('test_attempts_view');
    }

    public function view(User $user, TestAttempt $attempt): bool
    {
        // Admin/super admin bisa lihat semua attempt
        if ($user->can('test_attempts_view')) {
            return true;
        }

        // User hanya bisa lihat attempt sendiri
        return $user->can('test_attempts_view_own') && $attempt->user_id === $user->id;
    }

    /**
     * User mulai/mengerjakan test — hanya untuk attempt milik sendiri.
     */
    public function take(User $user, TestProgramWeek $week): bool
    {
        return $user->can('test_attempts_view_own');
    }

    /**
     * User lihat riwayat sendiri.
     */
    public function viewOwnHistory(User $user): bool
    {
        return $user->can('test_attempts_view_own');
    }

    /**
     * Admin export hasil test.
     */
    public function exportExcel(User $user): bool
    {
        return $user->can('test_attempts_view');
    }

    public function exportPdf(User $user): bool
    {
        return $user->can('test_attempts_view');
    }
}
