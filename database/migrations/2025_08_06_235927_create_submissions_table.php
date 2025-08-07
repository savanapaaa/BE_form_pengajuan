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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'content_creation',
                'publication_request', 
                'media_upload',
                'document_submission',
                'other'
            ])->default('content_creation');
            
            // Status Tracking
            $table->enum('status', [
                'draft',
                'submitted',
                'in_review',
                'approved',
                'rejected',
                'in_validation',
                'validated',
                'published',
                'completed'
            ])->default('draft');
            
            $table->enum('workflow_stage', [
                'creation',
                'submission',
                'review',
                'validation',
                'publication',
                'completed'
            ])->default('creation');
            
            // Confirmation and Priority
            $table->boolean('is_confirmed')->default(false);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Assignment
            $table->foreignId('assigned_reviewer')->nullable()->constrained('users');
            $table->foreignId('assigned_validator')->nullable()->constrained('users');
            
            // Timestamps for workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('deadline')->nullable();
            
            // Additional metadata
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'workflow_stage']);
            $table->index('submitted_at');
            $table->index(['assigned_reviewer', 'status']);
            $table->index(['assigned_validator', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
