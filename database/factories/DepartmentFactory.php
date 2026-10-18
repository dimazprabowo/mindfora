<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Produksi', 'Quality Control', 'Maintenance', 'HRD', 'Gudang', 'Logistik', 'Engineering', 'Keuangan']);

        return [
            'code' => strtoupper(fake()->lexify('???')),
            'name' => $name,
            'is_active' => true,
        ];
    }
}
