<?php

namespace App\Http\Requests;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'holiday_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Holiday::withTrashed()->whereDate('holiday_date', $value)->exists()) {
                        $fail('The holiday date has already been taken.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:150'],
            'holiday_type' => ['nullable', 'string', Rule::in(Holiday::types())],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'holiday_date' => $this->filled('holiday_date') ? trim((string) $this->input('holiday_date')) : null,
            'holiday_type' => $this->filled('holiday_type') ? strtolower(trim((string) $this->input('holiday_type'))) : null,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
