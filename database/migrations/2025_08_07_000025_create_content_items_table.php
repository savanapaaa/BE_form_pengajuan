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
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            
            // Content type: 'text', 'image', 'video', 'audio', 'document', 'link', 'file'
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'link', 'file'])
                  ->default('text');
            
            $table->string('title')->nullable();
            $table->longText('content')->nullable(); // For text content, descriptions, etc.
            
            // File related fields
            $table->string('file_path')->nullable(); // Local file path
            $table->string('file_url')->nullable();  // External URL or CDN URL
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            
            // Display and ordering
            $table->integer('order_index')->default(0); // For sorting content items
            $table->boolean('is_published')->default(false);
            
            // Additional metadata (JSON field for flexible data)
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['submission_id', 'order_index']);
            $table->index(['type', 'is_published']);
            $table->index('order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};