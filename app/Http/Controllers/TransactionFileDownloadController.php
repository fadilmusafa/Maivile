<?php

namespace App\Http\Controllers;

use App\Models\TransactionFile;
use App\Services\ActivityLogger;
use App\Services\TransactionFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Satu-satunya jalan untuk membuka lampiran transaksi (storage privat).
 * Otorisasi lewat TransactionFilePolicy::download: Admin, atau siswa pemilik transaksi.
 */
class TransactionFileDownloadController extends Controller
{
    public function __invoke(Request $request, TransactionFile $file): StreamedResponse
    {
        Gate::authorize('download', $file);

        $disk = Storage::disk(TransactionFileStorage::DISK);
        abort_unless($disk->exists($file->file_path), 404, 'Berkas tidak ditemukan.');

        $inline = $request->boolean('inline');
        $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);
        $name = (Str::slug(pathinfo($file->original_name, PATHINFO_FILENAME)) ?: 'lampiran').'.'.$ext;

        ActivityLogger::log($inline ? 'transaction_file_viewed' : 'transaction_file_downloaded', $file, "Lampiran {$file->original_name}");

        return $disk->response($file->file_path, $name, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], $inline ? 'inline' : 'attachment');
    }
}
