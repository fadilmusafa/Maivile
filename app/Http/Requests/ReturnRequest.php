<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date', 'before_or_equal:today'],
            'return_condition_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'return_date' => 'Tanggal pengembalian',
            'return_condition_note' => 'Kondisi rapor',
        ];
    }
}
