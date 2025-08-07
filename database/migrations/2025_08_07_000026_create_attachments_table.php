<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys - Polymorphic relationship
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('cascade');
            $table->foreignId('content_item_id')->nullable()->constrained('content_items')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            
            // File Information
            $table->string('original_name'); // Original filename from user
            $table->string('file_name'); // Stored filename (usually hashed)
            $table->string('file_path'); // Full path to file
            $table->string('file_url')->nullable(); // Public URL if accessible
            $table->string('disk')->default('local'); // Storage disk (local, s3, etc.)
            
            // File Properties
            $table->string('mime_type')->nullable();
            $table->string('file_extension', 10)->nullable();
            $table->bigInteger('file_size')->default(0); // Size in bytes
            $table->string('file_hash')->nullable(); // For duplicate detection
            
            // File Type Classification
            $table->enum('file_type', [
                'image',
                'video', 
                'audio',
                'document',
                'archive',
                'other'
            ])->default('other');
            
            // Image/Video specific (if applicable)
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration')->nullable(); // For video/audio in seconds
            
            // Thumbnail/Preview
            $table->string('thumbnail_path')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->boolean('has_preview')->default(false);
            
            // Status and Security
            $table->enum('status', [
                'uploading',
                'processing', 
                'completed',
                'failed',
                'quarantined'
            ])->default('uploading');
            
            $table->boolean('is_public')->default(false);
            $table->boolean('is_virus_scanned')->default(false);
            $table->boolean('is_safe')->default(true);
            
            // Upload Information
            $table->string('upload_session_id')->nullable();
            $table->json('upload_metadata')->nullable(); // Browser info, etc.
            $table->timestamp('upload_completed_at')->nullable();
            
            // Access Control
            $table->json('access_permissions')->nullable(); // Who can access
            $table->integer('download_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            
            // Processing Information
            $table->json('processing_metadata')->nullable(); // Processing results
            $table->text('processing_errors')->nullable();
            $table->boolean('is_processed')->default(false);
            
            // Lifecycle
            $table->timestamp('expires_at')->nullable(); // Auto-delete date
            $table->boolean('is_temporary')->default(false);
            
            // Additional metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Flexible additional data
            $table->text('alt_text')->nullable(); // For accessibility
            $table->text('caption')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['submission_id', 'file_type']);
            $table->index(['content_item_id', 'status']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['file_type', 'status']);
            $table->index('file_hash'); // For duplicate detection
            $table->index(['is_public', 'status']);
            $table->index('upload_completed_at');
            $table->index('expires_at');
            
            // Constraints
            // $table->check('file_size >= 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
