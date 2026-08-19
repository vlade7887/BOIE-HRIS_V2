<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveDraftRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d'],
            'requested_unit' => ['required', 'numeric', Rule::in([0.5, 1, '0.5', '1'])],
            'half_day_period' => ['nullable', 'string', Rule::in(['AM', 'PM'])],
            'returned_to_work_date' => ['nullable', 'date_format:Y-m-d'],
            'reason' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $unit = (float) $this->input('requested_unit', 1);
        $this->merge([
            'requested_unit' => $unit,
            'half_day_period' => $unit === 1.0 ? null : strtoupper(trim((string) $this->input('half_day_period'))),
        ]);
    }
}
