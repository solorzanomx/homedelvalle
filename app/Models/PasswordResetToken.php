<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'token', 'expiration_date', 'used'])]
class PasswordResetToken extends Model
{
    protected $table = 'custom_password_resets';

    protected function casts(): array
    {
        return [
            'expiration_date' => 'datetime',
            'used' => 'boolean',
        ];
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(64));
    }

    public static function createForEmail(string $email): self
    {
        // Invalidar tokens previos no usados para este email
        static::where('email', $email)
            ->where('used', false)
            ->update(['used' => true]);

        return static::create([
            'email' => $email,
            'token' => static::generateToken(),
            'expiration_date' => now()->addMinutes(30),
            'used' => false,
        ]);
    }

    public static function findValidToken(string $token): ?self
    {
        return static::where('token', $token)
            ->where('used', false)
            ->where('expiration_date', '>', now())
            ->first();
    }

    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }
}
