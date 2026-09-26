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
                'department' => null,
            ]
        );

        $mentorDepartments = [
            'Mentor User' => 'web_developer',
            'Android Mentor' => 'android_developer',
            'iOS Mentor' => 'ios_developer',
            'DevOps Mentor' => 'devops',
            'AI Mentor' => 'ai_developer',
            'BA Mentor' => 'business_analyst',
            'Data Mentor' => 'data_analyst',
        ];

        foreach ($mentorDepartments as $name => $dept) {
            $email = $name === 'Mentor User' ? 'mentor@example.com' : strtolower(str_replace(' ', '', $name)) . '@example.com';
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => 'mentor',
                    'department' => $dept,
                ]
            );
        }

        User::firstOrCreate(
            ['email' => 'intern@example.com'],
            [
                'name' => 'Intern User',
                'password' => Hash::make('password123'),
                'role' => 'intern',
                'department' => 'web_developer',
            ]
        );

        if (class_exists(\Faker\Factory::class)) {
            User::factory(50)->create([
                'role' => 'intern',
            ]);
        }
    }
}
