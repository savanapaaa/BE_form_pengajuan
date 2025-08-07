<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // User Management
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'category' => 'user'],
            ['name' => 'view_users', 'display_name' => 'View Users', 'category' => 'user'],
            
            // Form Management  
            ['name' => 'create_forms', 'display_name' => 'Create Forms', 'category' => 'form'],
            ['name' => 'edit_forms', 'display_name' => 'Edit Forms', 'category' => 'form'],
            ['name' => 'view_forms', 'display_name' => 'View Forms', 'category' => 'form'],
            ['name' => 'delete_forms', 'display_name' => 'Delete Forms', 'category' => 'form'],
            
            // Review & Validation
            ['name' => 'review_content', 'display_name' => 'Review Content', 'category' => 'review'],
            ['name' => 'validate_content', 'display_name' => 'Validate Content', 'category' => 'validation'],
            ['name' => 'publish_content', 'display_name' => 'Publish Content', 'category' => 'validation'],
            
            // Reports
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'category' => 'report'],
            ['name' => 'export_data', 'display_name' => 'Export Data', 'category' => 'report'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                array_merge($permission, [
                    'description' => 'Permission to ' . strtolower($permission['display_name']),
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}