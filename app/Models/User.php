<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    protected $fillable = ['name', 'last_name', 'email', 'password', 'phone', 'whatsapp', 'address', 'avatar_path', 'role', 'is_active', 'can_read', 'can_edit', 'can_delete', 'bio', 'title', 'branch', 'language', 'timezone', 'email_signature', 'show_phone_on_properties', 'shared_card_type'];
    protected $hidden = ['password', 'remember_token'];
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_read' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
            'show_phone_on_properties' => 'boolean',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->name . ' ' . ($this->last_name ?? ''));
    }

    public function mailSetting(): HasOne
    {
        return $this->hasOne(UserMailSetting::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderByDesc('created_at');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }

    public function assignedClients()
    {
        return $this->hasMany(Client::class, 'assigned_user_id');
    }

    // ─── RBAC ──────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function getCachedRbacData(): array
    {
        return cache()->remember("user_{$this->id}_rbac", now()->addMinutes(30), function () {
            $roles = $this->roles()->with('permissions')->get();
            return [
                'role_slugs' => $roles->pluck('slug')->toArray(),
                'permission_slugs' => $roles->pluck('permissions')->flatten()->pluck('slug')->unique()->values()->toArray(),
            ];
        });
    }

    public function clearPermissionCache(): void
    {
        cache()->forget("user_{$this->id}_rbac");
    }

    public function getAllPermissions(): Collection
    {
        return collect($this->getCachedRbacData()['permission_slugs']);
    }

    public function isSuperAdmin(): bool
    {
        return in_array('super_admin', $this->getCachedRbacData()['role_slugs']);
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->getAllPermissions()->contains($slug);
    }

    public function hasAnyPermission(string ...$permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        $userPermissions = $this->getAllPermissions();
        foreach ($permissions as $p) {
            if ($userPermissions->contains($p)) {
                return true;
            }
        }
        return false;
    }

    public function hasRole(string $roleSlug): bool
    {
        return in_array($roleSlug, $this->getCachedRbacData()['role_slugs']);
    }

    public function hasAnyRole(string ...$roleSlugs): bool
    {
        $userRoles = $this->getCachedRbacData()['role_slugs'];
        foreach ($roleSlugs as $slug) {
            if (in_array($slug, $userRoles)) {
                return true;
            }
        }
        return false;
    }
}
