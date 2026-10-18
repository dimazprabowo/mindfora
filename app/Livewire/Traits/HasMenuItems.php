<?php

namespace App\Livewire\Traits;

use App\Models\Chat;
use App\Models\Company;
use App\Models\Department;
use App\Models\Notification;
use App\Models\SystemConfiguration;
use App\Models\TestProgram;
use App\Models\TestProgramAssignment;
use App\Models\TrainingMaterial;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

trait HasMenuItems
{
    public function getMenuItems(): array
    {
        $user = Auth::user();

        // Cache per request — Sidebar and Navigation both call this, avoid double DB hit
        static $cache = [];
        $cacheKey = 'menu_'.$user->id;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        // All checks go through Gate → policies (respects Gate::before super-admin bypass)
        $perms = [
            'dashboard_view' => Gate::allows('viewStats'),
            'companies_view' => Gate::allows('viewAny', Company::class),
            'departments_view' => Gate::allows('viewAny', Department::class),
            'test_programs_view' => Gate::allows('viewAny', TestProgram::class),
            'test_assignments_view' => Gate::allows('viewAny', TestProgramAssignment::class),
            'test_attempts_view' => Gate::allows('viewAny', \App\Models\TestAttempt::class),
            'test_attempts_view_own' => Gate::allows('viewOwnHistory', \App\Models\TestAttempt::class),
            'training_materials_view' => Gate::allows('viewAny', TrainingMaterial::class),
            'training_materials_view_public' => Gate::allows('viewLibrary', TrainingMaterial::class),
            'consultation_start' => Gate::allows('consultation_start') || Gate::allows('viewAny', Chat::class),
            'notifications_view' => Gate::allows('viewAny', Notification::class),
            'notifications_send' => Gate::allows('send', Notification::class),
            'chat_view' => Gate::allows('viewAny', Chat::class),
            'configuration_view' => Gate::allows('viewAny', SystemConfiguration::class),
            'users_view' => Gate::allows('viewAny', User::class),
            'roles_view' => Gate::allows('viewAny', Role::class),
        ];

        $req = request();
        $items = [];

        // Dashboard — all roles
        $items[] = [
            'name' => __('menu.dashboard'),
            'route' => 'dashboard',
            'icon' => 'home',
            'active' => $req->routeIs('dashboard'),
        ];

        // Master Data
        $masterDataChildren = [];
        if ($perms['companies_view']) {
            $masterDataChildren[] = [
                'name' => __('menu.companies'),
                'route' => 'master-data.companies',
                'active' => $req->routeIs('master-data.companies'),
            ];
        }
        if ($perms['departments_view']) {
            $masterDataChildren[] = [
                'name' => __('menu.departments'),
                'route' => 'master-data.departments',
                'active' => $req->routeIs('master-data.departments'),
            ];
        }
        if (! empty($masterDataChildren)) {
            $items[] = [
                'name' => __('menu.master_data'),
                'icon' => 'database',
                'active' => $req->routeIs('master-data.*'),
                'children' => $masterDataChildren,
            ];
        }

        // Program Test (admin/super admin)
        $programChildren = [];
        if ($perms['test_programs_view']) {
            $programChildren[] = [
                'name' => __('menu.program_test'),
                'route' => 'programs.index',
                'active' => $req->routeIs('programs.*'),
            ];
        }
        if ($perms['test_assignments_view']) {
            $programChildren[] = [
                'name' => __('menu.scheduling'),
                'route' => 'assignments.index',
                'active' => $req->routeIs('assignments.*'),
            ];
        }
        if ($perms['test_attempts_view']) {
            $programChildren[] = [
                'name' => __('menu.test_results'),
                'route' => 'test-attempts.index',
                'active' => $req->routeIs('test-attempts.*'),
            ];
        }
        if (! empty($programChildren)) {
            $items[] = [
                'name' => __('menu.program_test'),
                'icon' => 'clipboard-check',
                'active' => $req->routeIs('programs.*', 'assignments.*', 'test-attempts.*'),
                'children' => $programChildren,
            ];
        }

        // Materi Pelatihan (admin manage / user library)
        if ($perms['training_materials_view']) {
            $items[] = [
                'name' => __('menu.training_materials'),
                'route' => 'training.manage',
                'icon' => 'play-circle',
                'active' => $req->routeIs('training.manage*'),
            ];
        } elseif ($perms['training_materials_view_public']) {
            $items[] = [
                'name' => __('menu.training'),
                'route' => 'training.index',
                'icon' => 'play-circle',
                'active' => $req->routeIs('training.index*'),
            ];
        }

        // My Test (user/peserta — dashboard status 4 minggu)
        if ($perms['test_attempts_view_own']) {
            $items[] = [
                'name' => __('menu.my_test'),
                'route' => 'my-test.index',
                'icon' => 'academic-cap',
                'active' => $req->routeIs('my-test.*'),
            ];
        }

        // Riwayat (user/peserta)
        if ($perms['test_attempts_view_own']) {
            $items[] = [
                'name' => __('menu.history'),
                'route' => 'history.index',
                'icon' => 'chart-bar',
                'active' => $req->routeIs('history.*'),
            ];
        }

        // Konsultasi (user — mulai konsultasi / admin — chat)
        if ($perms['consultation_start'] && ! $perms['chat_view']) {
            $items[] = [
                'name' => __('menu.consultation'),
                'route' => 'consultation.index',
                'icon' => 'user-group',
                'active' => $req->routeIs('consultation.*'),
            ];
        }

        // Notifikasi
        $canView = $perms['notifications_view'];
        $canSend = $perms['notifications_send'];

        if ($canView || $canSend) {
            $notifChildren = [];
            if ($canView) {
                $notifChildren[] = [
                    'name' => __('menu.inbox'),
                    'route' => 'notifications.index',
                    'active' => $req->routeIs('notifications.index'),
                ];
            }
            if ($canSend) {
                $notifChildren[] = [
                    'name' => __('menu.send_notification'),
                    'route' => 'notifications.send',
                    'active' => $req->routeIs('notifications.send'),
                ];
            }

            if ($canView && $canSend) {
                $items[] = [
                    'name' => __('menu.notifications'),
                    'icon' => 'bell',
                    'active' => $req->routeIs('notifications.*'),
                    'children' => $notifChildren,
                ];
            } else {
                $items[] = [
                    'name' => __('menu.notifications'),
                    'route' => $canView ? 'notifications.index' : 'notifications.send',
                    'icon' => 'bell',
                    'active' => $req->routeIs('notifications.*'),
                ];
            }
        }

        // Chat
        if ($perms['chat_view']) {
            $items[] = [
                'name' => __('menu.chat'),
                'route' => 'chat.index',
                'icon' => 'chat',
                'active' => $req->routeIs('chat.*'),
            ];
        }

        // Pengaturan
        $settingsChildren = [];
        if ($perms['configuration_view']) {
            $settingsChildren[] = [
                'name' => __('menu.system_configuration'),
                'route' => 'settings.system',
                'active' => $req->routeIs('settings.system'),
            ];
        }
        if ($perms['users_view']) {
            $settingsChildren[] = [
                'name' => __('menu.user_management'),
                'route' => 'settings.users',
                'active' => $req->routeIs('settings.users'),
            ];
        }
        if ($perms['roles_view']) {
            $settingsChildren[] = [
                'name' => __('menu.roles_permissions'),
                'route' => 'settings.roles',
                'active' => $req->routeIs('settings.roles'),
            ];
        }
        if (! empty($settingsChildren)) {
            $items[] = [
                'name' => __('menu.settings'),
                'icon' => 'cog',
                'active' => $req->routeIs('settings.*'),
                'children' => $settingsChildren,
            ];
        }

        $result = array_filter($items);
        $cache[$cacheKey] = $result;

        return $result;
    }
}
