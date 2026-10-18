<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Super Admin - All permissions (bypass via Gate::before in AppServiceProvider)
        $superAdmin = Role::firstOrCreate(['name' => 'super admin']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin - Read-only all data lintas company (view + export only, no create/update/delete/publish)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'dashboard_view',
            // Master data — read-only
            'companies_view',
            'companies_export_excel',
            'companies_export_pdf',
            'departments_view',
            'departments_export_excel',
            'departments_export_pdf',
            // Program test — read-only
            'test_programs_view',
            'test_programs_export_excel',
            'test_programs_export_pdf',
            'test_assignments_view',
            'test_attempts_view',
            // Training — read-only
            'training_materials_view',
            // Users & roles — read-only
            'users_view',
            'users_export_excel',
            'users_export_pdf',
            'roles_view',
            'roles_export_excel',
            'roles_export_pdf',
            // Konfigurasi — read-only
            'configuration_view',
            'configuration_export_excel',
            'configuration_export_pdf',
            // Notifikasi
            'notifications_view',
            'notifications_send',
            // Chat
            'chat_view',
            'chat_create',
            'chat_delete',
        ]);

        // 3. User/Peserta - Akses terbatas (dashboard, test sendiri, pelatihan, konsultasi)
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'test_attempts_view_own',
            'training_materials_view_public',
            'consultation_start',
            'notifications_view',
            'chat_view',
            'chat_create',
        ]);
    }
}
