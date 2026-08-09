<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_number' => (string) fake()->unique()->numberBetween(1000, 9999),
            'plate_number' => fake()->unique()->bothify('##-##-####'),
            'type' => fake()->randomElement(['شاحنة', 'صهريج', 'باص', 'سيارة']),
            'category' => fake()->randomElement(['نقل ثقيل', 'نقل خفيف', 'خدمات']),
            'model' => fake()->randomElement(['Mercedes', 'Isuzu', 'Hino', 'Toyota']),
            'manufacture_year' => fake()->numberBetween(2000, date('Y')),
            'color' => fake()->colorName(),
            'chassis_number' => fake()->unique()->bothify('CH-#######'),
            'engine_number' => fake()->unique()->bothify('EN-#######'),
            'fuel_type' => fake()->randomElement(['gasoline', 'diesel']),
            'engine_capacity' => fake()->randomElement(['1000cc', '2000cc', '4000cc']),
            'management_id' => null,
            'status' => fake()->randomElement(['active', 'maintenance', 'stopped', 'sold', 'out_of_service']),
            'current_mileage' => fake()->randomFloat(2, 0, 500000),
            'operating_hours' => fake()->randomFloat(2, 0, 20000),
        ];
    }
}
