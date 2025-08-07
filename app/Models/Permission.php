<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category'
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    public function roles()
    {
        return $this->belongsToMany(User::class, 'role_permissions', 'permission_id', 'role')
                    ->distinct();
    }
}