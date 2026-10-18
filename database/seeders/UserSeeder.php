<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $produksiDept = Department::where('code', 'PROD')->first();
        $hrdDept = Department::where('code', 'HRD')->first();

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@app.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '021-1234566',
                'position' => 'Super Administrator',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $superAdmin->hasRole('super admin')) {
            $superAdmin->assignRole('super admin');
        }

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'phone' => '021-1234567',
                'position' => 'System Administrator',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // User / Peserta
        $user = User::firstOrCreate(
            ['email' => 'user@app.com'],
            [
                'name' => 'Sample User',
                'password' => Hash::make('password'),
                'phone' => '021-1234568',
                'position' => 'Staff Produksi',
                'gender' => 'L',
                'birth_date' => '1990-05-15',
                'education_level' => 'sma',
                'department_id' => $produksiDept?->id,
                'work_started_at' => '2020-01-01',
                'locale' => 'id',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        if (! $user->hasRole('user')) {
            $user->assignRole('user');
        }
    }
}
