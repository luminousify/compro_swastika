<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'force_password_change',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'role' => UserRole::class,
            'force_password_change' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user is sales
     */
    public function isSales(): bool
    {
        return $this->role === UserRole::SALES;
    }

    /**
     * Check if user can access a resource
     */
    public function canAccess(string $resource): bool
    {
        return match ($resource) {
            'settings', 'users' => $this->isAdmin(),
            'divisions', 'products', 'technologies', 'machines',
            'media', 'milestones', 'clients', 'contact_messages' => true,
            default => false,
        };
    }

    /**
     * Get uploaded media relationship
     */
    public function uploadedMedia()
    {
        return $this->hasMany(Media::class, 'uploaded_by');
    }
}
