<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'pin_hash',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::OWNER;
    }

    public function isCashier(): bool
    {
        return $this->role === UserRole::CASHIER;
    }

    public function setPin(string $pin): void
    {
        $this->pin_hash = Hash::make($pin);
        $this->save();
    }

    public function verifyPin(string $pin): bool
    {
        if (! $this->pin_hash) {
            return false;
        }
        return Hash::check($pin, $this->pin_hash);
    }
}
