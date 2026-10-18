<?php

namespace Database\Factories;

use App\Enums\TestProgramStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestProgram>
 */
class TestProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Program '.fake()->word(),
            'description' => fake()->optional()->paragraph(),
            'status' => TestProgramStatus::Draft,
            'created_by' => 1,
        ];
    }
}
