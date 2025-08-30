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
        Schema::table('content_items', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('content_items', 'reviewed_by')) {
                // Review fields
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('reviewed_at')->nullable();
                $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('review_notes')->nullable();
                
                // Validation fields
                $table->foreignId('validation_assigned_to')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('validation_assigned_at')->nullable();
                $table->timestamp('validated_at')->nullable();
                $table->enum('validation_status', ['pending', 'validated', 'published', 'rejected'])->default('pending');
                $table->text('validation_notes')->nullable();
                $table->date('publish_date')->nullable();
                $table->json('published_content')->nullable();
                
                // Workflow stage for each content item
                $table->enum('workflow_stage', ['review', 'validation', 'completed'])->default('review');
                
                // Indexes for better performance
                $table->index(['reviewed_by', 'review_status'], 'ci_review_idx');
                $table->index(['validation_assigned_to', 'validation_status'], 'ci_validation_idx');
                $table->index(['workflow_stage', 'review_status'], 'ci_workflow_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_items', function (Blueprint $table) {
            if (Schema::hasColumn('content_items', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropForeign(['validation_assigned_to']);
                $table->dropForeign(['validated_by']);
                
                $table->dropIndex('ci_review_idx');
                $table->dropIndex('ci_validation_idx');
                $table->dropIndex('ci_workflow_idx');
                
                $table->dropColumn([
                    'reviewed_by',
                    'reviewed_at',
                    'review_status',
                    'review_notes',
                    'validation_assigned_to',
                    'validated_by',
                    'validation_assigned_at',
                    'validated_at',
                    'validation_status',
                    'validation_notes',
                    'publish_date',
                    'published_content',
                    'workflow_stage'
                ]);
            }
        });
    }
};
