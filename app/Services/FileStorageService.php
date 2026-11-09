<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Semua file privat disimpan di disk "local" (storage/app/private), BUKAN di public/.
 * Nama file dibuat acak (UUID) dan ekstensi ditentukan dari isi file, bukan dari nama kiriman pengguna.
 */
class FileStorageService
{
    public const DISK = 'local';

    private const MIME_TO_EXT = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /** @return array{0: string, 1: string} [path, mime] */
    public function storeDocument(UploadedFile $file): array
    {
        $mime = (string) $file->getMimeType();
        $ext = self::MIME_TO_EXT[$mime] ?? null;

        if ($ext === null) {
            throw new InvalidArgumentException('Jenis berkas tidak diizinkan.');
        }

        $path = $file->storeAs('documents', Str::uuid()->toString().'.'.$ext, ['disk' => self::DISK]);

        if ($path === false) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        return [$path, $mime];
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
