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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users');
            
            // Review Status
            $table->enum('status', [
                'pending',
                'in_progress', 
                'approved',
                'rejected',
                'needs_revision'
            ])->default('pending');
            
            // Review Content
            $table->text('notes')->nullable();
            $table->longText('feedback')->nullable();
            $table->json('checklist')->nullable(); // For review criteria checklist
            
            // Scoring (optional)
            $table->integer('score')->nullable(); // 1-10 or 1-100 scale
            $table->enum('recommendation', [
                'approve',
                'reject', 
                'revise',
                'escalate'
            ])->nullable();
            
            // Assignment and Timing
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Review rounds (for revisions)
            $table->integer('review_round')->default(1);
            $table->foreignId('parent_review_id')->nullable()->constrained('reviews');
            
            // Additional data
            $table->json('metadata')->nullable();
            $table->boolean('is_final')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['submission_id', 'status']);
            $table->index(['reviewer_id', 'status']);
            $table->index('assigned_at');
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
