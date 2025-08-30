<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,      // Run first
            UserSeeder::class,            // Then users
            RolePermissionSeeder::class,  // Finally role permissions
            SubmissionSeeder::class,      // Submissions
            ContentItemSeeder::class,     // Content items
        ]);
    }
}