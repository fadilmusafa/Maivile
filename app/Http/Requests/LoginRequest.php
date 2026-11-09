<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Email atau NIS/NIP wajib diisi.',
            'login.max' => 'Email atau NIS/NIP terlalu panjang.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }
}
