<?php

namespace Database\Seeders;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create another regular user
        $regularUser = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Create files for test user
        File::factory()->count(5)->create([
            'user_id' => $testUser->id,
            'is_published' => true,
        ]);

        File::factory()->count(3)->create([
            'user_id' => $testUser->id,
            'is_published' => false,
        ]);

        // Create files for regular user
        File::factory()->count(8)->create([
            'user_id' => $regularUser->id,
        ]);
    }
}