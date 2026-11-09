<?php

namespace App\Services;

use App\Enums\DocumentAvailability;
use App\Enums\DocumentKind;
use App\Models\Document;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class DocumentService
{
    private const FIELDS = [
        'category_id', 'owner_user_id', 'name', 'document_number', 'academic_year',
        'description', 'document_kind', 'physical_location',
    ];

    public function __construct(private FileStorageService $files)
    {
    }

    public function create(array $data, ?UploadedFile $file): Document
    {
        $newPath = null;

        try {
            return DB::transaction(function () use ($data, $file, &$newPath) {
                $doc = new Document();
                $doc->fill(Arr::only($data, self::FIELDS));
                $doc->availability = $data['availability'] ?? DocumentAvailability::Tersedia->value;

                if ($doc->document_kind === DocumentKind::Digital && $file) {
                    [$newPath, $mime] = $this->files->storeDocument($file);
                    $doc->file_path = $newPath;
                    $doc->file_mime = $mime;
                }

                $doc->save();

                return $doc;
            });
        } catch (Throwable $e) {
            $this->files->delete($newPath); // jangan tinggalkan file yatim
            throw $e;
        }
    }

    public function update(Document $doc, array $data, ?UploadedFile $file): Document
    {
        $oldPath = $doc->file_path;
        $newPath = null;

        try {
            DB::transaction(function () use ($doc, $data, $file, &$newPath) {
                $doc->fill(Arr::only($data, self::FIELDS));

                // Status "dipinjam" hanya diubah oleh alur peminjaman, bukan lewat form ini.
                if ($doc->availability !== DocumentAvailability::Dipinjam) {
                    $doc->availability = $data['availability'] ?? $doc->availability->value;
                }

                if ($doc->document_kind === DocumentKind::Physical) {
                    $doc->file_path = null;
                    $doc->file_mime = null;
                } elseif ($file) {
                    [$newPath, $mime] = $this->files->storeDocument($file);
                    $doc->file_path = $newPath;
                    $doc->file_mime = $mime;
                }

                $doc->save();
            });
        } catch (Throwable $e) {
            $this->files->delete($newPath);
            throw $e;
        }

        if ($oldPath && $oldPath !== $doc->file_path) {
            $this->files->delete($oldPath); // hapus file lama setelah commit berhasil
        }

        return $doc;
    }

    public function delete(Document $doc): void
    {
        if ($doc->transactions()->withTrashed()->exists()) {
            throw new DomainException('Dokumen ini sudah dipakai dalam transaksi sehingga tidak dapat dihapus.');
        }

        $doc->delete(); // soft delete; file fisik dipertahankan
    }
}
