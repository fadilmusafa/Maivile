<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Kode layanan tidak bisa diubah karena alur status bergantung padanya. */
class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
