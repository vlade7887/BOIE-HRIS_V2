<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $baseId = $this->route('base')?->id ?? $this->route('base');

        return [
            'base_code' => ['required', 'string', 'max:20', Rule::unique('bases', 'base_code')->ignore($baseId)],
            'base_name' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
