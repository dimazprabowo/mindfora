<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Single source of truth untuk semua permissions.
     *
     * Idempotent — aman dijalankan berulang kali di production:
     *   php artisan db:seed --class=PermissionSeeder
     *
     * Konvensi penamaan:
     *   {entity}_{action}
     *   entity : dashboard, companies, configuration, users, roles, notifications, chat
     *   action : view, create, update, delete, export_excel, export_pdf, send
     *
     * Format ini memudahkan grouping otomatis di UI berdasarkan entity prefix.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard_view',

            // Master Data — Perusahaan
            'companies_view',
            'companies_create',
            'companies_update',
            'companies_delete',
            'companies_export_excel',
            'companies_export_pdf',

            // Master Data — Departemen
            'departments_view',
            'departments_create',
            'departments_update',
            'departments_delete',
            'departments_export_excel',
            'departments_export_pdf',

            // Program Test
            'test_programs_view',
            'test_programs_create',
            'test_programs_update',
            'test_programs_delete',
            'test_programs_export_excel',
            'test_programs_export_pdf',
            'test_programs_publish',

            // Program Assignment (penjadwalan ke periode/bulan)
            'test_assignments_view',
            'test_assignments_create',
            'test_assignments_update',
            'test_assignments_delete',

            // Hasil Test / Attempt
            'test_attempts_view',        // admin/super admin — lihat hasil semua peserta
            'test_attempts_view_own',    // user — riwayat sendiri

            // Materi Pelatihan
            'training_materials_view',
            'training_materials_create',
            'training_materials_update',
            'training_materials_delete',
            'training_materials_view_public', // user — akses library materi

            // Konsultasi
            'consultation_start',

            // Konfigurasi System
            'configuration_view',
            'configuration_update',
            'configuration_export_excel',
            'configuration_export_pdf',

            // Manajemen User
            'users_view',
            'users_create',
            'users_update',
            'users_delete',
            'users_export_excel',
            'users_export_pdf',
            'users_impersonate',

            // Roles & Permissions
            'roles_view',
            'roles_create',
            'roles_update',
            'roles_delete',
            'roles_export_excel',
            'roles_export_pdf',

            // Notifikasi
            'notifications_view',
            'notifications_send',

            // Chat / Pesan
            'chat_view',
            'chat_create',
            'chat_delete',

            // Profile - Company Management
            'manage_own_company',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
