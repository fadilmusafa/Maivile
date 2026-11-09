<?php

namespace App\Enums;

enum ProcessStatus: string
{
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case PerluPerbaikan = 'perlu_perbaikan';
    case Disetujui = 'disetujui';
    case JadwalDikonfirmasi = 'jadwal_dikonfirmasi';
    case SedangDiproses = 'sedang_diproses';
    case SiapDiambil = 'siap_diambil';
    case Dipinjam = 'dipinjam';
    case Dikembalikan = 'dikembalikan';
    case Diserahkan = 'diserahkan';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }

    public function badge(): string
    {
        return match ($this) {
            self::MenungguVerifikasi, self::PerluPerbaikan => 'warning',
            self::Ditolak => 'danger',
            self::Selesai => 'secondary',
            default => 'info',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Selesai, self::Ditolak], true);
    }
}
