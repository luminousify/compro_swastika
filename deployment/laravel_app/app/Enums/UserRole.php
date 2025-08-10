<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SALES = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::SALES => 'Sales',
        };
    }

    public function canAccessSettings(): bool
    {
        return $this === self::ADMIN;
    }

    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }
}
