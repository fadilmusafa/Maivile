<?php

namespace App\Http\Requests;

use App\Services\TransactionWorkflowService as Wf;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class ReviewTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $needsNote = in_array($this->input('decision'), [Wf::DECISION_REJECT, Wf::DECISION_REVISE], true);

        return [
            'decision' => ['required', Rule::in([Wf::DECISION_APPROVE, Wf::DECISION_REJECT, Wf::DECISION_REVISE])],
            'note' => [Rule::requiredIf($needsNote), 'nullable', 'string', 'max:1000'],
            'planned_date' => ['nullable', 'date', 'after_or_equal:today',
                function (string $attribute, mixed $value, Closure $fail) {
                    try {
                        if (Carbon::parse((string) $value)->isWeekend()) {
                            $fail('Jadwal pengambilan harus pada hari kerja (Senin–Jumat).');
                        }
                    } catch (Throwable) {
                        // format tanggal sudah dicek oleh aturan "date"
                    }
                }],
        ];
    }

    public function attributes(): array
    {
        return [
            'decision' => 'Keputusan',
            'note' => 'Catatan',
            'planned_date' => 'Jadwal pengambilan',
        ];
    }

    public function messages(): array
    {
        return ['note.required' => 'Catatan wajib diisi saat menolak atau meminta perbaikan.'];
    }
}
