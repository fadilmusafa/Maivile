<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Dipakai untuk tambah dan ubah pengguna (Admin / Siswa). */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi oleh middleware role:admin pada route
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');
        $isCreate = $user === null;
        $role = $isCreate ? $this->input('role') : $user->role;
        $ident = ['string', 'max:30', 'regex:/^[0-9A-Za-z.\-]+$/'];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                $role === User::ROLE_ADMIN ? 'required' : 'nullable',
                'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($isCreate) {
            $rules['role'] = ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STUDENT])];
            $rules['password'] = ['nullable', 'string', 'min:8', 'max:255', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'];
        }

        if ($role === User::ROLE_STUDENT) {
            $rules['nis'] = array_merge(['required'], $ident, [Rule::unique('users', 'nis')->ignore($user?->id)]);
            $rules['class_name'] = ['required', 'string', 'max:30'];
            $rules['major'] = ['nullable', 'string', 'max:50'];
        } else {
            $rules['nip'] = array_merge(['nullable'], $ident, [Rule::unique('users', 'nip')->ignore($user?->id)]);
        }

        return $rules;
    }
}
