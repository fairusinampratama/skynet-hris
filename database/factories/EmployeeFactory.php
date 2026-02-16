<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
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
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'join_date' => $this->faker->date(),
            'basic_salary' => $this->faker->numberBetween(3000000, 10000000),
            'role_type' => $this->faker->randomElement(['office', 'technician']),
            'profile_photo_path' => null,
            'face_descriptor' => null,
        ];
    }
}
