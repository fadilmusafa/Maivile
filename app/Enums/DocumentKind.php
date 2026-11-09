<?php

namespace App\Enums;

enum DocumentKind: string
{
    case Digital = 'digital';
    case Physical = 'physical';

    public function label(): string
    {
        return match ($this) {
            self::Digital => 'Digital',
            self::Physical => 'Fisik',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Digital => 'info',
            self::Physical => 'secondary',
        };
    }
}
