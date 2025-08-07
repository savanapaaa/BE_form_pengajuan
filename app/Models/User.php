<?php

namespace App\Models;

use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function validations()
    {
        return $this->hasMany(Validation::class, 'validator_id');
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    public function hasAnyPermission($permissions)
    {
        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withPivot('granted')
                    ->withTimestamps();
    }

    public function hasPermission($permission)
    {
        return $this->permissions()
                    ->where('name', $permission)
                    ->where('user_permissions.granted', true)
                    ->exists();
    }

    public function grantPermission($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['granted' => true, 'granted_at' => now()]
            ]);
        }
    }

    public function revokePermission($permission)
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->syncWithoutDetaching([
                $permissionModel->id => ['granted' => false]
            ]);
        }
    }
}