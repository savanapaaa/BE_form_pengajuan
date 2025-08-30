<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviewer = User::where('role', 'reviewer')->first() ?? 
                   User::whereIn('role', ['admin', 'superadmin'])->first();
        
        $submissions = Submission::limit(3)->get(); // Review first 3 submissions
        
        $reviewStatuses = ['approved', 'approved', 'rejected'];
        $recommendations = ['approve', 'approve', 'reject'];
        $reviewNotes = [
            'Submission sangat baik dan sesuai dengan guidelines. Konten dapat dilanjutkan ke tahap validasi.',
            'Ide kreatif dan execution yang solid. Siap untuk masuk ke fase produksi konten.',
            'Submission perlu perbaikan pada aspek target audience dan messaging strategy.'
        ];

        foreach ($submissions as $index => $submission) {
            Review::create([
                'submission_id' => $submission->id,
                'reviewer_id' => $reviewer->id,
                'status' => $reviewStatuses[$index],
                'notes' => $reviewNotes[$index],
                'feedback' => $reviewStatuses[$index] === 'approved' ? 
                    'Submission telah memenuhi semua kriteria dan dapat dilanjutkan.' :
                    'Mohon lakukan revisi sesuai dengan catatan yang diberikan.',
                'score' => $reviewStatuses[$index] === 'approved' ? rand(8, 10) : rand(5, 7),
                'recommendation' => $recommendations[$index],
                'assigned_by' => $reviewer->id,
                'assigned_at' => now()->subDays(rand(3, 7)),
                'started_at' => now()->subDays(rand(1, 3)),
                'completed_at' => now()->subHours(rand(1, 24)),
                'due_date' => now()->addDays(rand(5, 10)),
                'is_final' => true
            ]);
        }
    }
}
