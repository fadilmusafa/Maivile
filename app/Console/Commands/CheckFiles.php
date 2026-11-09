<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\TransactionFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckFiles extends Command
{
    protected $signature = 'maivile:files-check
                            {--delete : Hapus berkas yatim (tanpa opsi ini hanya menampilkan daftar)}
                            {--min-age=24 : Umur minimal berkas dalam jam agar dianggap yatim}
                            {--force : Hapus tanpa konfirmasi}';

    protected $description = 'Cari berkas di storage yang tidak terdaftar di database, dan data yang berkasnya hilang';

    private const DISK = 'local';

    /** Folder yang dikelola aplikasi (lihat FileStorageService dan TransactionFileStorage). */
    private const FOLDERS = ['documents', 'transaction-files'];

    public function handle(): int
    {
        $disk = Storage::disk(self::DISK);
        $minAge = max(0, (int) $this->option('min-age'));
        $cutoff = now()->subHours($minAge)->getTimestamp();

        // Dokumen yang sudah di-soft-delete tetap memegang berkasnya, jadi ikut dihitung.
        $referenced = Document::withTrashed()->whereNotNull('file_path')->pluck('file_path')
            ->merge(TransactionFile::query()->whereNotNull('file_path')->pluck('file_path'))
            ->map(fn ($p) => $this->normalize($p))
            ->flip();

        $orphans = [];
        foreach (self::FOLDERS as $folder) {
            foreach ($disk->files($folder) as $path) {
                $path = $this->normalize($path);

                if (str_starts_with(basename($path), '.') || $referenced->has($path)) {
                    continue;
                }

                if ($disk->lastModified($path) <= $cutoff) {
                    $orphans[] = $path;
                }
            }
        }

        $missing = [];
        foreach (Document::withTrashed()->whereNotNull('file_path')->get(['id', 'name', 'file_path']) as $d) {
            if (! $disk->exists($d->file_path)) {
                $missing[] = "Dokumen #{$d->id} ({$d->name}): {$d->file_path}";
            }
        }
        foreach (TransactionFile::query()->get(['id', 'transaction_id', 'file_path']) as $f) {
            if (! $disk->exists($f->file_path)) {
                $missing[] = "Lampiran #{$f->id} (pengajuan #{$f->transaction_id}): {$f->file_path}";
            }
        }

        $this->reportMissing($missing);

        if ($orphans === []) {
            $this->info("Tidak ada berkas yatim (lebih tua dari {$minAge} jam).");

            return $missing === [] ? self::SUCCESS : self::FAILURE;
        }

        $this->warn(count($orphans).' berkas yatim (ada di storage, tidak ada di database):');
        foreach ($orphans as $path) {
            $this->line('  '.$path);
        }

        if (! $this->option('delete')) {
            $this->line('');
            $this->line('Tidak ada yang dihapus. Tambahkan --delete untuk menghapus berkas yatim di atas.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Hapus '.count($orphans).' berkas yatim ini secara permanen?', false)) {
            $this->line('Dibatalkan, tidak ada yang dihapus.');

            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($orphans as $path) {
            if ($disk->delete($path)) {
                $deleted++;
            }
        }
        $this->info("{$deleted} berkas yatim dihapus.");

        return self::SUCCESS;
    }

    private function reportMissing(array $missing): void
    {
        if ($missing === []) {
            return;
        }

        $this->error(count($missing).' data punya berkas yang HILANG dari storage (perlu dipulihkan dari backup):');
        foreach ($missing as $line) {
            $this->line('  '.$line);
        }
        $this->line('');
    }

    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }
}
