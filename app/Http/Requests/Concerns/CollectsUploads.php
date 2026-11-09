<?php

namespace App\Http\Requests\Concerns;

use App\Models\TransactionFile;
use Illuminate\Http\UploadedFile;

trait CollectsUploads
{
    /** Aturan umum satu berkas lampiran: PDF/JPG/PNG, maksimal 2 MB (dicek dari isi file). */
    protected function fileRules(): array
    {
        return ['file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'max:2048'];
    }

    /**
     * Mengubah berkas yang diunggah menjadi daftar [file, type] untuk TransactionWorkflowService.
     *
     * @return array<int, array{file: UploadedFile, type: string}>
     */
    public function uploads(): array
    {
        $items = [];

        foreach ((array) $this->file('attachments', []) as $file) {
            if ($file instanceof UploadedFile) {
                $items[] = ['file' => $file, 'type' => TransactionFile::TYPE_REQUIREMENT];
            }
        }

        $proxy = $this->file('power_of_attorney');
        if ($proxy instanceof UploadedFile && $this->boolean('represented')) {
            $items[] = ['file' => $proxy, 'type' => TransactionFile::TYPE_POWER_OF_ATTORNEY];
        }

        return $items;
    }
}
