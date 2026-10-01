<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_BHW = 'bhw';
    public const ROLE_CITIZEN = 'citizen';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdmin()
    {
        // Often a superadmin should also pass admin checks
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN]);
    }

    public function isBhw()
    {
        return $this->role === self::ROLE_BHW;
    }

    public function isCitizen()
    {
        return $this->role === self::ROLE_CITIZEN;
    }

    public function citizen() 
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function permissions()
{
    return $this->hasMany(\App\Models\UserPermission::class);
}

// Helper method to check if user has write access to a specific feature
public function hasWriteAccess(string $feature): bool
{
    if ($this->role === 'admin') {
        return true; // Admins always have write access everywhere
    }

    $permission = $this->permissions()->where('feature', $feature)->first();
    $access = $permission ? $permission->access : 'none';
    
    // Default to 'read' (false for write) if not explicitly set
    return $permission && $permission->access === 'write';
}

public function hasReadAccess($feature)
{
    $permission = $this->permissions()->where('feature', $feature)->first();
    $access = $permission ? $permission->access : 'none'; // default fallback

    // 'read' and 'write' both allow reading, but 'none' blocks it
    return in_array($access, ['read', 'write']);
}

}