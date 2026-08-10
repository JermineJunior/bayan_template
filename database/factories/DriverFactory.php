<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'national_id' => (string) fake()->unique()->numerify('##-##########-#'),
            'phone_number' => fake()->numerify('09########'),
            'department_id' => null,
            'hire_date' => fake()->date(),
            'license_type' => fake()->randomElement(['general', 'private', 'other']),
            'license_expiry_date' => fake()->date(),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
