<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->userName() . fake()->numberBetween(100, 99999) . '@example.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'intern',
            'department' => fake()->randomElement(\App\Enums\Department::values()),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * State for Admin user.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * State for Mentor user.
     */
    public function mentor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'mentor',
        ]);
    }

    /**
     * State for Intern user.
     */
    public function intern(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'intern',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
