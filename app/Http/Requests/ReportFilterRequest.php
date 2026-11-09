<?php

namespace App\Http\Requests;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $to = ['nullable', 'date_format:Y-m-d'];
        if ($this->filled('from')) {
            $to[] = 'after_or_equal:from';
        }

        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => $to,
            'service_type_id' => ['nullable', 'integer', 'exists:service_types,id'],
            'approval' => ['nullable', Rule::enum(ApprovalStatus::class)],
            'process' => ['nullable', Rule::enum(ProcessStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'from' => 'tanggal awal',
            'to' => 'tanggal akhir',
            'service_type_id' => 'layanan',
            'approval' => 'status persetujuan',
            'process' => 'status proses',
        ];
    }

    /** Filter yang sudah tervalidasi dan dinormalkan. */
    public function filters(): array
    {
        $v = $this->validated();

        return [
            'from' => $v['from'] ?? null,
            'to' => $v['to'] ?? null,
            'service_type_id' => isset($v['service_type_id']) ? (int) $v['service_type_id'] : null,
            'approval' => $v['approval'] ?? null,
            'process' => $v['process'] ?? null,
        ];
    }
}
