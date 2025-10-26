<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-30 days', 'now');
        $checkOut = fake()->optional(0.8)->dateTimeBetween($checkIn, '+10 hours');

        return [
            'employee_id' => \App\Models\Employee::factory(),
            'location_id' => \App\Models\Location::factory(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
