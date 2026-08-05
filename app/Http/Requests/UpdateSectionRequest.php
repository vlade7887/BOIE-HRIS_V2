<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sectionId = $this->route('section')?->id ?? $this->route('section');

        return [
            'department_id' => ['nullable', 'exists:departments,id'],
            'section_code' => ['required', 'string', 'max:20', Rule::unique('sections', 'section_code')->ignore($sectionId)],
            'section_name' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
