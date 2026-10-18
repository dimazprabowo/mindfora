<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Foundation (urutan dependency wajib)
            PermissionSeeder::class,
            RoleSeeder::class,
            CompanySeeder::class,
            DepartmentSeeder::class,
            SystemConfigurationSeeder::class,
            UserSeeder::class,

            // Mindfulness modules (urutan dependency: program -> assignment -> attempt)
            TestProgramSeeder::class,
            TestProgramAssignmentSeeder::class,
            TrainingMaterialSeeder::class,
            TestAttemptSeeder::class,
        ]);
    }
}
