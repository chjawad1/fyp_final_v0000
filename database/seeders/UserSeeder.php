<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SupervisorProfile; // 👈 ADD THIS LINE
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder is safe to run multiple times.
     */
    public function run(): void
    {
        // 1. Create or update the Administrator
        User::updateOrCreate(
            ['email' => 'admin@fyp.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]
        );

        // 2. Create or update the sample Supervisor and their profile
        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@fyp.com'],
            [
                'name' => 'Dr. Supervisor',
                'password' => Hash::make('12345678'),
                'role' => 'supervisor',
            ]
        );

        // 👇 START OF NEW BLOCK TO CREATE THE SUPERVISOR'S PROFILE
        // Check if a profile needs to be created for this supervisor
        if ($supervisor->wasRecentlyCreated || !$supervisor->supervisorProfile) {
            SupervisorProfile::create([
                'user_id' => $supervisor->id,
                'research_interests' => 'Artificial Intelligence, Machine Learning',
                'available_slots' => 8, // Default slots as per SRS
            ]);
        }
        // 👆 END OF NEW BLOCK

        // 3. Create or update the sample Student
        User::updateOrCreate(
            ['email' => 'student@fyp.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('12345678'),
                'role' => 'student',
            ]
        );
    }
}