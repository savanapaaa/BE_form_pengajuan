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
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignId('validator_id')->constrained('users');
            
            // Validation Status
            $table->enum('status', [
                'pending',
                'in_progress',
                'validated',
                'published',
                'rejected',
                'needs_revision'
            ])->default('pending');
            
            // Validation Content
            $table->text('notes')->nullable();
            $table->longText('validation_feedback')->nullable();
            $table->json('validation_criteria')->nullable(); // Criteria checklist
            
            // Publication Details
            $table->timestamp('publish_date')->nullable();
            $table->json('published_content')->nullable(); // Final published content
            $table->string('publication_url')->nullable();
            $table->text('publication_notes')->nullable();
            
            // Quality Assurance
            $table->integer('quality_score')->nullable(); // 1-100 quality score
            $table->boolean('meets_standards')->default(false);
            $table->json('compliance_check')->nullable(); // Legal/policy compliance
            
            // Assignment and Timing
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('due_date')->nullable();
            
            // Validation rounds
            $table->integer('validation_round')->default(1);
            $table->foreignId('parent_validation_id')->nullable()->constrained('validations');
            
            // Additional data
            $table->json('metadata')->nullable();
            $table->boolean('is_final')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['submission_id', 'status']);
            $table->index(['validator_id', 'status']);
            $table->index('publish_date');
            $table->index(['status', 'due_date']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
