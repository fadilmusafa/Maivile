<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\CollectsUploads;
use App\Models\Transaction;
use App\Models\TransactionFile;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Dipakai untuk pengajuan baru (store) dan untuk perbaikan pengajuan (resubmit).
 * Pada perbaikan, route memuat parameter {transaction}.
 */
class DiplomaRequest extends FormRequest
{
    use CollectsUploads;

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->isStudent()) {
            return false;
        }

        $transaction = $this->route('transaction');

        return $transaction instanceof Transaction ? $transaction->user_id === $user->id : true;
    }

    public function rules(): array
    {
        $transaction = $this->route('transaction');
        $hasProxyFile = $transaction instanceof Transaction
            && $transaction->files()->where('type', TransactionFile::TYPE_POWER_OF_ATTORNEY)->exists();
        $represented = $this->boolean('represented');

        return [
            'document_id' => ['nullable', 'integer', Rule::exists('documents', 'id')
                ->where('owner_user_id', $this->user()->id)->whereNull('deleted_at')],
            'planned_date' => ['required', 'date', 'after_or_equal:today',
                function (string $attribute, mixed $value, Closure $fail) {
                    try {
                        if (Carbon::parse((string) $value)->isWeekend()) {
                            $fail('Pengambilan ijazah hanya dapat dijadwalkan pada hari kerja (Senin–Jumat).');
                        }
                    } catch (Throwable) {
                        // format tanggal sudah dicek oleh aturan "date"
                    }
                }],
            'pickup_time_start' => ['nullable', 'date_format:H:i'],
            'pickup_time_end' => ['nullable', 'date_format:H:i', 'after:pickup_time_start'],
            'contact_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'represented' => ['nullable', 'boolean'],
            'receiver_name' => [Rule::requiredIf($represented), 'nullable', 'string', 'max:100'],
            'receiver_relation' => [Rule::requiredIf($represented), 'nullable', 'string', 'max:50'],
            'power_of_attorney' => [
                Rule::requiredIf($represented && ! $hasProxyFile),
                'nullable',
                ...$this->fileRules(),
            ],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => $this->fileRules(),
        ];
    }

    public function attributes(): array
    {
        return [
            'document_id' => 'Dokumen ijazah',
            'planned_date' => 'Tanggal rencana pengambilan',
            'pickup_time_start' => 'Jam mulai',
            'pickup_time_end' => 'Jam selesai',
            'contact_phone' => 'Kontak yang dapat dihubungi',
            'description' => 'Catatan tambahan',
            'receiver_name' => 'Nama pengambil (perwakilan)',
            'receiver_relation' => 'Hubungan dengan siswa',
            'power_of_attorney' => 'Surat kuasa',
            'attachments' => 'Dokumen pendukung',
            'attachments.*' => 'Dokumen pendukung',
        ];
    }

    public function messages(): array
    {
        return [
            'power_of_attorney.required' => 'Surat kuasa wajib diunggah jika ijazah diambil oleh orang lain.',
        ];
    }
}
