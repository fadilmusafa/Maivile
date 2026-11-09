<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Siswa hanya boleh mengubah nomor telepon.
     * Nama, NIS, kelas, jurusan dikelola oleh sekolah.
     * Field lain yang dikirim akan diabaikan karena tidak ada di rules.
     */
    public function rules(): array
    {
        $rules = [
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ];

        if ($this->user()->isAdmin()) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, - dan tanda kurung.',
        ];
    }
}
