<?php

namespace App\Livewire\Pages;

use App\Enums\AttemptStatus;
use App\Enums\StressCategory;
use App\Models\Company;
use App\Models\TestAttempt;
use App\Models\TestProgram;
use App\Models\TestProgramAssignment;
use App\Models\TrainingMaterial;
use App\Models\User;
use App\Services\TestAttemptService;
use App\Services\TrainingMaterialService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $canViewStats = Gate::allows('dashboard_view');
        $isPeserta = Gate::allows('viewOwnHistory', TestAttempt::class);

        $data = [
            'authUser' => $user,
            'authUserRole' => $user->getRoleNames()->join(', ') ?: 'User',
            'canViewStats' => $canViewStats,
            'isPeserta' => $isPeserta,
            'appJoinedAt' => $user->created_at,
        ];

        if ($canViewStats) {
            // Admin stats
            if (Gate::allows('users_view')) {
                $data['totalUsers'] = User::count();
            }
            if (Gate::allows('companies_view')) {
                $data['totalCompanies'] = Company::count();
            }
            if (Gate::allows('roles_view')) {
                $data['totalRoles'] = Role::count();
            }
            if (Gate::allows('test_programs_view')) {
                $data['totalPrograms'] = TestProgram::count();
                $data['activePrograms'] = TestProgram::where('status', 'active')->count();
            }
            if (Gate::allows('test_assignments_view')) {
                $data['totalAssignments'] = TestProgramAssignment::count();
                $data['activeAssignmentCount'] = TestProgramAssignment::where('status', 'active')->count();
            }
            if (Gate::allows('test_attempts_view')) {
                $data['totalAttempts'] = TestAttempt::where('status', AttemptStatus::Submitted)->count();
                // Stress distribution
                $data['stressDistribution'] = [
                    'rendah' => TestAttempt::where('status', AttemptStatus::Submitted)->where('score_category', StressCategory::Low)->count(),
                    'sedang' => TestAttempt::where('status', AttemptStatus::Submitted)->where('score_category', StressCategory::Medium)->count(),
                    'tinggi' => TestAttempt::where('status', AttemptStatus::Submitted)->where('score_category', StressCategory::High)->count(),
                ];
            }
            if (Gate::allows('training_materials_view')) {
                $data['totalMaterials'] = TrainingMaterial::count();
                $data['publishedMaterials'] = TrainingMaterial::where('is_published', true)->count();
            }
        }

        // Peserta stats — their active assignment + week progress
        if ($isPeserta) {
            $service = app(TestAttemptService::class);
            $assignment = $service->getActiveAssignmentForUser($user);

            $data['activeAssignment'] = $assignment;
            $data['currentWeek'] = $assignment?->getCurrentWeekNumber();

            if ($assignment) {
                $weekStatuses = $service->getWeekStatusesForUser($user, $assignment);
                $completedWeeks = collect($weekStatuses)->filter(fn ($s) => $s['best_score'] !== null)->count();
                $data['completedWeeks'] = $completedWeeks;
                $data['weekStatuses'] = $weekStatuses;
                $data['bestScore'] = collect($weekStatuses)->max('best_score');
            }

            // Latest published training materials for preview (only if user can view public library)
            if (Gate::allows('training_materials_view_public')) {
                $data['latestMaterials'] = app(TrainingMaterialService::class)->getLatestPublished(3);
            }
        }

        return view('livewire.pages.dashboard', $data);
    }
}
