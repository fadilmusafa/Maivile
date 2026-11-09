<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\CollectsUploads;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HandoverRequest extends FormRequest
{
    use CollectsUploads;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $notSelf = ! $this->boolean('receiver_is_self');

        return [
            'handover_date' => ['required', 'date', 'before_or_equal:today'],
            'receiver_is_self' => ['required', 'boolean'],
            'receiver_name' => [Rule::requiredIf($notSelf), 'nullable', 'string', 'max:100'],
            'receiver_relation' => [Rule::requiredIf($notSelf), 'nullable', 'string', 'max:50'],
            'receiver_id_number' => ['nullable', 'string', 'max:50'],
            'handover_note' => ['nullable', 'string', 'max:1000'],
            'proof' => ['required', ...$this->fileRules()],
        ];
    }

    public function attributes(): array
    {
        return [
            'handover_date' => 'Tanggal penyerahan',
            'receiver_is_self' => 'Penerima',
            'receiver_name' => 'Nama penerima',
            'receiver_relation' => 'Hubungan dengan siswa',
            'receiver_id_number' => 'Nomor identitas penerima',
            'handover_note' => 'Catatan penyerahan',
            'proof' => 'Bukti serah terima',
        ];
    }

    public function messages(): array
    {
        return ['proof.required' => 'Bukti serah terima (foto atau scan tanda terima) wajib diunggah.'];
    }
}
