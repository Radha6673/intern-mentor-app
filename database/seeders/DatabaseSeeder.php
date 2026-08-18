<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'mentor@example.com'],
            [
                'name' => 'Mentor User',
                'password' => Hash::make('password123'),
                'role' => 'mentor',
            ]
        );

        User::firstOrCreate(
            ['email' => 'intern@example.com'],
            [
                'name' => 'Intern User',
                'password' => Hash::make('password123'),
                'role' => 'intern',
            ]
        );

        User::factory(500)->create([
            'role' => 'intern',
        ]);
    }
}
