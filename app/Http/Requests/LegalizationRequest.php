<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\CollectsUploads;
use App\Services\TransactionWorkflowService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LegalizationRequest extends FormRequest
{
    use CollectsUploads;

    public const DOC_TYPES = ['Ijazah', 'Rapor', 'Surat Keterangan', 'Dokumen akademik lain'];

    public function authorize(): bool
    {
        return $this->user()?->isStudent() ?? false;
    }

    public function rules(): array
    {
        return [
            'legalization_doc_type' => ['required', Rule::in(self::DOC_TYPES)],
            'document_id' => ['nullable', 'integer', Rule::exists('documents', 'id')
                ->where('owner_user_id', $this->user()->id)->whereNull('deleted_at')],
            'copies' => ['required', 'integer', 'min:1', 'max:'.TransactionWorkflowService::MAX_COPIES],
            'description' => ['required', 'string', 'max:500'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'attachments' => ['required', 'array', 'min:1', 'max:3'],
            'attachments.*' => $this->fileRules(),
        ];
    }

    public function attributes(): array
    {
        return [
            'legalization_doc_type' => 'Jenis dokumen',
            'document_id' => 'Dokumen',
            'copies' => 'Jumlah salinan',
            'description' => 'Keperluan legalisir',
            'contact_phone' => 'Nomor telepon',
            'attachments' => 'Lampiran persyaratan',
            'attachments.*' => 'Lampiran persyaratan',
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.required' => 'Lampirkan minimal satu berkas persyaratan (misalnya fotokopi dokumen).',
            'attachments.min' => 'Lampirkan minimal satu berkas persyaratan (misalnya fotokopi dokumen).',
        ];
    }
}
