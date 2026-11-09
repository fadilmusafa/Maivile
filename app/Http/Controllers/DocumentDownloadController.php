<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\ActivityLogger;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Satu-satunya jalan untuk membuka file dokumen. File ada di storage privat,
 * sehingga setiap akses WAJIB lewat pemeriksaan DocumentPolicy::download.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request, Document $document): StreamedResponse
    {
        Gate::authorize('download', $document); // admin, atau pemilik dokumen

        $disk = Storage::disk(FileStorageService::DISK);
        abort_unless($disk->exists($document->file_path), 404, 'Berkas tidak ditemukan.');

        $inline = $request->boolean('inline');
        $ext = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $name = (Str::slug($document->name) ?: 'dokumen').'.'.$ext;

        ActivityLogger::log($inline ? 'document_viewed' : 'document_downloaded', $document, "Berkas {$document->name}");

        return $disk->response($document->file_path, $name, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], $inline ? 'inline' : 'attachment');
    }
}
