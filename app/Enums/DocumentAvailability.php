<?php

namespace App\Enums;

enum DocumentAvailability: string
{
    case Tersedia = 'tersedia';
    case Dipinjam = 'dipinjam';
    case TidakTersedia = 'tidak_tersedia';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Dipinjam => 'Dipinjam',
            self::TidakTersedia => 'Tidak tersedia',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Tersedia => 'success',
            self::Dipinjam => 'info',
            self::TidakTersedia => 'secondary',
        };
    }
}
