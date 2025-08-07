<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Get all permissions for reference
        $permissions = DB::table('permissions')->pluck('id', 'name');
        
        $rolePermissions = [
            'superadmin' => [
                'manage_users',
                'view_users',
                'create_forms',
                'edit_own_forms',
                'edit_all_forms',
                'view_own_forms',
                'view_all_forms',
                'delete_forms',
                'review_content',
                'approve_content',
                'reject_content',
                'assign_reviewers',
                'validate_content',
                'publish_content',
                'final_approval',
                'view_reports',
                'export_data',
                'view_statistics',
                'advanced_analytics',
                'admin_access',
                'system_settings',
                'view_logs'
            ],
            
            'admin' => [
                'manage_users',
                'view_users',
                'edit_all_forms',
                'view_all_forms',
                'delete_forms',
                'review_content',
                'approve_content',
                'reject_content',
                'assign_reviewers',
                'validate_content',
                'publish_content',
                'view_reports',
                'export_data',
                'view_statistics',
                'admin_access'
            ],
            
            'form' => [
                'create_forms',
                'edit_own_forms',
                'view_own_forms'
            ],
            
            'review' => [
                'view_all_forms',
                'review_content',
                'approve_content',
                'reject_content',
                'view_reports'
            ],
            
            'validasi' => [
                'view_all_forms',
                'validate_content',
                'publish_content',
                'final_approval',
                'view_reports'
            ],
            
            'rekap' => [
                'view_all_forms',
                'view_reports',
                'export_data',
                'view_statistics'
            ]
        ];

        // Clear existing role permissions
        DB::table('role_permissions')->truncate();

        // Insert new role permissions
        foreach ($rolePermissions as $role => $permissionNames) {
            foreach ($permissionNames as $permissionName) {
                if (isset($permissions[$permissionName])) {
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $permissions[$permissionName],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}