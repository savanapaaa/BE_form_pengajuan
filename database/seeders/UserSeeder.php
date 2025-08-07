<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing users (optional - comment out if you want to keep existing data)
        // User::truncate();

        // Super Administrator
        User::factory()->create([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('super123'), // password: super123
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        // Form User
        User::factory()->create([
            'name' => 'Form User',
            'username' => 'form_user',
            'email' => 'form_user@example.com',
            'password' => Hash::make('form123'), // password: form123
            'role' => 'form',
            'email_verified_at' => now(),
        ]);

        // Content Reviewer
        User::factory()->create([
            'name' => 'Content Reviewer',
            'username' => 'reviewer',
            'email' => 'reviewer@example.com',
            'password' => Hash::make('review123'), // password: review123
            'role' => 'review',
            'email_verified_at' => now(),
        ]);

        // Content Validator
        User::factory()->create([
            'name' => 'Content Validator',
            'username' => 'validator',
            'email' => 'validator@example.com',
            'password' => Hash::make('validasi123'), // password: validasi123
            'role' => 'validasi',
            'email_verified_at' => now(),
        ]);

        // Report User
        User::factory()->create([
            'name' => 'Report User',
            'username' => 'rekap_user',
            'email' => 'rekap_user@example.com',
            'password' => Hash::make('rekap123'), // password: rekap123
            'role' => 'rekap',
            'email_verified_at' => now(),
        ]);

        // Keep the existing admin user (or update it)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'), // password: admin123
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Additional demo users for testing (optional)
        User::factory()->create([
            'name' => 'Test Form User 2',
            'username' => 'form_user2',
            'email' => 'form_user2@example.com',
            'password' => Hash::make('form123'),
            'role' => 'form',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Test Reviewer 2',
            'username' => 'reviewer2',
            'email' => 'reviewer2@example.com',
            'password' => Hash::make('review123'),
            'role' => 'review',
            'email_verified_at' => now(),
        ]);
    }
}