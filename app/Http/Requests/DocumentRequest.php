<?php

namespace App\Http\Requests;

use App\Enums\DocumentAvailability;
use App\Enums\DocumentKind;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public const MAX_FILE_KB = 2048; // 2 MB

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Document|null $doc */
        $doc = $this->route('document');
        $isDigital = $this->input('document_kind') === DocumentKind::Digital->value;
        $needsFile = $isDigital && ! ($doc && $doc->hasDigitalFile());

        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'owner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')
                ->where('role', User::ROLE_STUDENT)->whereNull('deleted_at')],
            'document_number' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'string', 'max:20', 'regex:/^\d{4}(\/\d{4})?$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'document_kind' => ['required', Rule::enum(DocumentKind::class)],
            'availability' => ['nullable', Rule::in([
                DocumentAvailability::Tersedia->value,
                DocumentAvailability::TidakTersedia->value,
            ])],
            'physical_location' => ['nullable', 'string', 'max:100'],
            'file' => [
                $needsFile ? 'required' : 'nullable',
                'prohibited_if:document_kind,'.DocumentKind::Physical->value,
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:'.self::MAX_FILE_KB,
            ],
        ];
    }

    public function attributes(): array
    {
        return ['file' => 'berkas'];
    }
}
