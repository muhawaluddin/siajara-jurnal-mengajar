<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Guru = 'guru';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Guru => 'Guru',
        };
    }
}
