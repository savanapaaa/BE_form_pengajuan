<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentItem;
use App\Models\Submission;

class ContentItemSeeder extends Seeder
{
    public function run(): void
    {
        $submissions = Submission::all();

        foreach ($submissions as $submission) {
            // Create sample content items for each submission
            ContentItem::create([
                'submission_id' => $submission->id,
                'type' => 'text',
                'title' => 'Deskripsi Utama',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'order_index' => 1,
                'is_published' => false,
                'metadata' => [
                    'word_count' => 20,
                    'language' => 'id'
                ]
            ]);

            ContentItem::create([
                'submission_id' => $submission->id,
                'type' => 'image',
                'title' => 'Gambar Dokumentasi',
                'content' => 'Screenshot aplikasi dashboard',
                'file_path' => 'uploads/images/sample-image.jpg',
                'file_url' => 'https://example.com/sample-image.jpg',
                'original_filename' => 'dashboard-screenshot.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 1024000, // 1MB
                'order_index' => 2,
                'is_published' => false,
                'metadata' => [
                    'dimensions' => ['width' => 1920, 'height' => 1080],
                    'alt_text' => 'Dashboard screenshot',
                    'caption' => 'Tampilan dashboard aplikasi'
                ]
            ]);
        }
    }
}