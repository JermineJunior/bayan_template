<?php

namespace Database\Factories;

use App\Models\Management;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Management>
 */
class ManagementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->text(10),
            'status' => 'active',
        ];
    }
}
