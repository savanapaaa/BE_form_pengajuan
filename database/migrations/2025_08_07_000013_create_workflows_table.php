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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Who performed the action
            
            // Workflow Tracking
            $table->string('from_stage')->nullable(); // Previous stage
            $table->string('to_stage'); // Current/new stage
            $table->string('action'); // What action was performed
            
            // Action Details
            $table->enum('action_type', [
                'create',
                'submit',
                'assign',
                'review',
                'approve',
                'reject',
                'validate',
                'publish',
                'complete',
                'comment',
                'edit',
                'delete',
                'escalate'
            ]);
            
            // Status and Priority
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->nullable();
            
            // Content and Notes
            $table->text('notes')->nullable();
            $table->longText('description')->nullable(); // Detailed description of action
            $table->json('changes')->nullable(); // What fields were changed
            
            // Related Records
            $table->foreignId('related_review_id')->nullable()->constrained('reviews');
            $table->foreignId('related_validation_id')->nullable()->constrained('validations');
            
            // Timing
            $table->timestamp('performed_at'); // When action was performed
            $table->integer('duration_minutes')->nullable(); // How long action took
            
            // System Information
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            
            // Approval Chain
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('requires_approval')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['submission_id', 'performed_at']);
            $table->index(['user_id', 'action_type']);
            $table->index(['to_stage', 'performed_at']);
            $table->index('action_type');
            $table->index('performed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
