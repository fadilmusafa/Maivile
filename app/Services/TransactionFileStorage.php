<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Lampiran transaksi disimpan di disk privat "local" (storage/app/private/transaction-files).
 * Nama file acak (UUID), ekstensi ditentukan dari isi file. Akses hanya lewat controller berotorisasi.
 */
class TransactionFileStorage
{
    public const DISK = 'local';

    private const MIME_TO_EXT = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function store(Transaction $transaction, UploadedFile $file, string $type, User $uploader): TransactionFile
    {
        $mime = (string) $file->getMimeType();
        $ext = self::MIME_TO_EXT[$mime] ?? null;

        if ($ext === null) {
            throw new InvalidArgumentException('Jenis berkas tidak diizinkan.');
        }

        $size = (int) $file->getSize();
        $original = Str::limit(basename((string) $file->getClientOriginalName()), 200, '');
        $path = $file->storeAs('transaction-files', Str::uuid()->toString().'.'.$ext, ['disk' => self::DISK]);

        if ($path === false) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        return TransactionFile::create([
            'transaction_id' => $transaction->id,
            'uploaded_by' => $uploader->id,
            'type' => $type,
            'original_name' => $original !== '' ? $original : 'berkas.'.$ext,
            'file_path' => $path,
            'mime_type' => $mime,
            'file_size' => $size,
        ]);
    }

    /** @param  array<int, string|null>  $paths */
    public function deletePaths(array $paths): void
    {
        foreach (array_filter($paths) as $path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}
