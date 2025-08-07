<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            Submission::create([
                'user_id' => $user->id,
                'title' => 'Sample Submission - ' . $user->name,
                'description' => 'This is a sample submission for testing',
                'type' => 'content_creation',
                'status' => 'submitted',
                'workflow_stage' => 'review',
                'is_confirmed' => true,
                'submitted_at' => now(),
                'metadata' => [
                    'tags' => ['sample', 'test'],
                    'category' => 'general'
                ]
            ]);
        }
    }
}
