<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginChallenge extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $hidden = ['code_hash', 'password_fingerprint'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'denied_at' => 'datetime',
            'consumed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return ! $this->denied_at && ! $this->consumed_at
            && $this->expires_at->isFuture() && $this->attempts < 5;
    }

    public function matchesAccount(?User $user): bool
    {
        return $user && ! $user->isLockedOut() && $user->email === $this->email
            && hash_equals($this->password_fingerprint, hash('sha256', $user->password));
    }
}
