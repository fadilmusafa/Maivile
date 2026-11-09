<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Pendaftaran akun siswa. Hanya field di bawah yang dipakai;
 * role, is_active, dan status pendaftaran ditentukan server, bukan form.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:30', 'regex:/^[0-9A-Za-z.\-]+$/', Rule::unique('users', 'nis')],
            'class_name' => ['required', 'string', 'max:30'],
            'major' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => [
                'required', 'string', 'min:8', 'max:255', 'confirmed',
                'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'nis' => 'NIS',
            'class_name' => 'kelas',
            'major' => 'jurusan',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'password' => 'kata sandi',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.regex' => 'NIS hanya boleh berisi huruf, angka, titik, atau strip.',
            'nis.unique' => 'NIS ini sudah terdaftar. Jika Anda merasa belum mendaftar, hubungi petugas Tata Usaha.',
            'class_name.required' => 'Kelas wajib diisi.',
            'major.required' => 'Jurusan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, atau tanda kurung.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus memuat huruf besar, huruf kecil, dan angka.',
        ];
    }
}
