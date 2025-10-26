<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'employee_code' => fake()->unique()->bothify('EMP-####'),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'position' => fake()->jobTitle(),
            'notes' => fake()->optional()->paragraph(),
            'is_active' => true, // Default to active for tests
            'force_password_change' => false,
        ];
    }
}
