<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentItem;
use App\Models\Submission;
use App\Models\Review;
use App\Models\User;

class ContentItemSeeder extends Seeder
{
    public function run(): void
    {
        $submissions = Submission::with('reviews')->get();
        $validator = User::where('role', 'validator')->first();

        foreach ($submissions as $submission) {
            $approvedReviews = $submission->reviews()->where('status', 'approved')->get();
            
            if ($approvedReviews->count() > 0) {
                // Create content items only for approved submissions
                $reviewerIds = $approvedReviews->pluck('reviewer_id');
                
                // Generate realistic content based on submission title
                $contentData = $this->generateContentForSubmission($submission);
                
                foreach ($contentData as $index => $content) {
                    ContentItem::create([
                        'submission_id' => $submission->id,
                        'type' => $content['type'],
                        'title' => $content['title'],
                        'content' => $content['content'],
                        'file_path' => $content['file_path'] ?? null,
                        'file_url' => $content['file_url'] ?? null,
                        'original_filename' => $content['original_filename'] ?? null,
                        'mime_type' => $content['mime_type'] ?? null,
                        'file_size' => $content['file_size'] ?? null,
                        'order_index' => $index + 1,
                        'is_published' => false,
                        'metadata' => $content['metadata'],
                        // Review information
                        'reviewed_by' => $reviewerIds->first(),
                        'reviewed_at' => $approvedReviews->first()->completed_at,
                        'review_status' => 'approved',
                        'review_notes' => 'Content telah direview dan siap untuk validasi',
                        // Validation assignment for approved content
                        'validation_assigned_to' => $validator ? $validator->id : null,
                        'validation_assigned_at' => now(),
                        'workflow_stage' => 'validation'
                    ]);
                }
            }
        }
    }

    private function generateContentForSubmission(Submission $submission): array
    {
        $title = strtolower($submission->title);
        
        if (str_contains($title, 'skincare')) {
            return [
                [
                    'type' => 'text',
                    'title' => 'Konten Utama Campaign Skincare',
                    'content' => 'Kampanye skincare untuk produk baru dengan fokus pada bahan natural dan hasil klinis yang terbukti. Target audience: wanita 25-40 tahun dengan disposable income menengah ke atas.',
                    'metadata' => ['word_count' => 35, 'language' => 'id', 'category' => 'beauty']
                ],
                [
                    'type' => 'image',
                    'title' => 'Visual Campaign Mockup',
                    'content' => 'Mockup visual untuk campaign skincare',
                    'file_path' => 'uploads/campaigns/skincare-mockup.jpg',
                    'file_url' => 'https://storage.example.com/skincare-mockup.jpg',
                    'original_filename' => 'skincare-campaign-visual.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 2048000,
                    'metadata' => ['dimensions' => ['width' => 1920, 'height' => 1080], 'category' => 'marketing-visual']
                ]
            ];
        } elseif (str_contains($title, 'video') || str_contains($title, 'tutorial')) {
            return [
                [
                    'type' => 'video',
                    'title' => 'Script Video Tutorial',
                    'content' => 'Script lengkap untuk video tutorial dengan durasi 10-15 menit. Mencakup introduction, demo produk, dan call-to-action.',
                    'file_path' => 'uploads/scripts/tutorial-script.pdf',
                    'file_url' => 'https://storage.example.com/tutorial-script.pdf',
                    'original_filename' => 'video-tutorial-script.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 512000,
                    'metadata' => ['duration_estimate' => '12 minutes', 'format' => 'educational', 'language' => 'id']
                ],
                [
                    'type' => 'text',
                    'title' => 'Video Description & Tags',
                    'content' => 'Deskripsi video untuk platform YouTube dan social media lainnya, termasuk hashtags dan keyword optimization.',
                    'metadata' => ['word_count' => 150, 'platform' => 'youtube', 'seo_optimized' => true]
                ]
            ];
        } elseif (str_contains($title, 'investasi') || str_contains($title, 'blog')) {
            return [
                [
                    'type' => 'text',
                    'title' => 'Artikel Blog Investasi',
                    'content' => 'Artikel lengkap tentang strategi investasi jangka panjang untuk pemula. Mencakup tips, risiko, dan rekomendasi portfolio diversifikasi.',
                    'metadata' => ['word_count' => 1200, 'reading_time' => '6 minutes', 'category' => 'finance', 'seo_score' => 85]
                ],
                [
                    'type' => 'image',
                    'title' => 'Infografik Investasi',
                    'content' => 'Infografik yang menjelaskan konsep investasi dengan visual yang menarik',
                    'file_path' => 'uploads/infographics/investment-guide.png',
                    'file_url' => 'https://storage.example.com/investment-guide.png',
                    'original_filename' => 'investment-infographic.png',
                    'mime_type' => 'image/png',
                    'file_size' => 1536000,
                    'metadata' => ['dimensions' => ['width' => 1080, 'height' => 1350], 'type' => 'infographic']
                ]
            ];
        } elseif (str_contains($title, 'design') || str_contains($title, 'graphic')) {
            return [
                [
                    'type' => 'image',
                    'title' => 'Design Assets Collection',
                    'content' => 'Koleksi design assets untuk berbagai kebutuhan marketing',
                    'file_path' => 'uploads/designs/marketing-assets.zip',
                    'file_url' => 'https://storage.example.com/marketing-assets.zip',
                    'original_filename' => 'design-collection.zip',
                    'mime_type' => 'application/zip',
                    'file_size' => 5120000,
                    'metadata' => ['file_count' => 15, 'formats' => ['ai', 'png', 'jpg'], 'category' => 'design']
                ],
                [
                    'type' => 'text',
                    'title' => 'Design Guidelines',
                    'content' => 'Panduan penggunaan design assets termasuk color palette, typography, dan brand guidelines.',
                    'metadata' => ['word_count' => 500, 'category' => 'brand-guidelines', 'format' => 'documentation']
                ]
            ];
        } else {
            // Default content for podcast or other types
            return [
                [
                    'type' => 'audio',
                    'title' => 'Podcast Episode Script',
                    'content' => 'Script lengkap untuk episode podcast dengan durasi 45 menit. Mencakup opening, interview questions, dan closing.',
                    'file_path' => 'uploads/podcasts/episode-script.docx',
                    'file_url' => 'https://storage.example.com/episode-script.docx',
                    'original_filename' => 'podcast-script.docx',
                    'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'file_size' => 256000,
                    'metadata' => ['duration_estimate' => '45 minutes', 'format' => 'interview', 'language' => 'id']
                ],
                [
                    'type' => 'text',
                    'title' => 'Episode Show Notes',
                    'content' => 'Show notes untuk episode podcast termasuk timestamp, link referensi, dan rangkuman pembahasan.',
                    'metadata' => ['word_count' => 300, 'format' => 'show-notes', 'episode_number' => 1]
                ]
            ];
        }
    }
}