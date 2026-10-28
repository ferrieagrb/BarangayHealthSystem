<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CitizenHealthShare extends Model
{
    protected $fillable = [
        'citizen_id',
        'granted_by_user_id',
        'granted_at',
        'expires_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function citizen()
    {
        return $this->belongsTo(citizens::class, 'citizen_id');
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null && $this->expires_at?->isFuture() === true;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }
}
