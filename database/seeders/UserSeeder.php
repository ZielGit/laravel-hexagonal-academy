<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hexagonal-academy.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Instructor 1: John Doe
        $instructor1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@hexagonal-academy.test',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now(),
        ]);

        // Instructor 2: Jane Smith
        $instructor2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@hexagonal-academy.test',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now(),
        ]);

        // Student users
        User::factory()->count(10)->create([
            'role' => 'student',
        ]);

        $this->command->info('✅ Users seeded successfully');
    }
}
