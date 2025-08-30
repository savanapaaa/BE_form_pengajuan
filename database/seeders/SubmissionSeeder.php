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
        $user = User::where('role', 'user')->first() ?? User::first();
        
        $submissions = [
            [
                'title' => 'Kampanye Media Sosial - Produk Skincare Terbaru',
                'description' => 'Membuat konten untuk kampanye peluncuran produk skincare terbaru yang ramah lingkungan. Target audience: wanita usia 20-35 tahun.',
                'type' => 'content_creation',
                'metadata' => [
                    'tags' => ['skincare', 'beauty', 'eco-friendly'],
                    'category' => 'marketing',
                    'target_audience' => 'women 20-35',
                    'platform' => ['instagram', 'tiktok', 'facebook']
                ]
            ],
            [
                'title' => 'Video Tutorial - Cara Menggunakan Aplikasi Mobile',
                'description' => 'Pembuatan video tutorial step-by-step untuk menjelaskan fitur-fitur utama aplikasi mobile kepada pengguna baru.',
                'type' => 'media_upload',
                'metadata' => [
                    'tags' => ['tutorial', 'mobile-app', 'onboarding'],
                    'category' => 'education',
                    'duration' => '3-5 minutes',
                    'platform' => ['youtube', 'website']
                ]
            ],
            [
                'title' => 'Artikel Blog - Tips Investasi untuk Pemula',
                'description' => 'Menulis artikel komprehensif tentang tips dan strategi investasi yang cocok untuk pemula, dengan bahasa yang mudah dipahami.',
                'type' => 'content_creation',
                'metadata' => [
                    'tags' => ['investasi', 'keuangan', 'pemula'],
                    'category' => 'finance',
                    'word_count' => '1500-2000',
                    'platform' => ['blog', 'website']
                ]
            ],
            [
                'title' => 'Design Grafis - Banner Promo Akhir Tahun',
                'description' => 'Membuat design banner dan poster untuk promosi akhir tahun dengan tema festive dan eye-catching.',
                'type' => 'content_creation',
                'metadata' => [
                    'tags' => ['design', 'banner', 'promo', 'year-end'],
                    'category' => 'marketing',
                    'format' => ['JPG', 'PNG', 'PDF'],
                    'platform' => ['website', 'social-media', 'print']
                ]
            ],
            [
                'title' => 'Podcast Series - Interview Entrepreneur Muda',
                'description' => 'Produksi podcast series yang mengundang entrepreneur muda sukses untuk berbagi pengalaman dan tips bisnis.',
                'type' => 'media_upload',
                'metadata' => [
                    'tags' => ['podcast', 'entrepreneur', 'business', 'interview'],
                    'category' => 'business',
                    'duration' => '45-60 minutes',
                    'platform' => ['spotify', 'youtube', 'apple-podcast']
                ]
            ]
        ];

        foreach ($submissions as $index => $submissionData) {
            Submission::create([
                'user_id' => $user->id,
                'title' => $submissionData['title'],
                'description' => $submissionData['description'],
                'type' => $submissionData['type'],
                'status' => 'submitted',
                'workflow_stage' => 'review',
                'is_confirmed' => true,
                'priority' => ['normal', 'high', 'normal', 'high', 'normal'][$index],
                'submitted_at' => now()->subDays(rand(1, 7)),
                'deadline' => now()->addDays(rand(14, 30)),
                'metadata' => $submissionData['metadata']
            ]);
        }
    }
}
