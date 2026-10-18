<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'PROD', 'name' => 'Produksi'],
            ['code' => 'QC', 'name' => 'Quality Control'],
            ['code' => 'MNT', 'name' => 'Maintenance'],
            ['code' => 'HRD', 'name' => 'HRD'],
            ['code' => 'GUD', 'name' => 'Gudang'],
            ['code' => 'LOG', 'name' => 'Logistik'],
            ['code' => 'ENG', 'name' => 'Engineering'],
            ['code' => 'KEU', 'name' => 'Keuangan'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['code' => $dept['code']],
                ['name' => $dept['name'], 'is_active' => true]
            );
        }
    }
}
