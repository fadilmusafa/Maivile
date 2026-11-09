<?php

namespace App\Http\Requests;

use App\Enums\DocumentKind;
use App\Http\Requests\Concerns\CollectsUploads;
use App\Services\TransactionWorkflowService;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class LoanRequest extends FormRequest
{
    use CollectsUploads;

    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        $max = TransactionWorkflowService::MAX_LOAN_DAYS;

        return [
            'document_id' => ['required', 'integer', Rule::exists('documents', 'id')
                ->where('owner_user_id', $this->user()->id)
                ->where('document_kind', DocumentKind::Physical->value)
                ->whereNull('deleted_at')],
            'planned_date' => ['required', 'date', 'after_or_equal:today'],
            'planned_return_date' => ['required', 'date', 'after_or_equal:planned_date',
                function (string $attribute, mixed $value, Closure $fail) use ($max) {
                    try {
                        $start = Carbon::parse((string) $this->input('planned_date'));
                        if ($start->diffInDays(Carbon::parse((string) $value)) > $max) {
                            $fail("Lama peminjaman maksimal {$max} hari.");
                        }
                    } catch (Throwable) {
                        // format tanggal sudah dicek oleh aturan "date"
                    }
                }],
            'description' => ['required', 'string', 'max:500'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => $this->fileRules(),
        ];
    }

    public function attributes(): array
    {
        return [
            'document_id' => 'Rapor',
            'planned_date' => 'Tanggal rencana peminjaman',
            'planned_return_date' => 'Tanggal rencana pengembalian',
            'description' => 'Keperluan peminjaman',
            'contact_phone' => 'Nomor telepon',
            'attachments' => 'Lampiran',
            'attachments.*' => 'Lampiran',
        ];
    }
}
