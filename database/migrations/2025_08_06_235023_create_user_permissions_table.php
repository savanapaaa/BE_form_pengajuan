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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->boolean('granted')->default(true); // true = granted, false = revoked
            $table->foreignId('granted_by')->nullable()->constrained('users');
            $table->timestamp('granted_at')->nullable();
            $table->text('notes')->nullable(); // Reason for granting/revoking
            $table->timestamps();
            
            // Ensure unique user-permission combinations
            $table->unique(['user_id', 'permission_id']);
            
            // Indexes for better performance
            $table->index(['user_id', 'granted']);
            $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
