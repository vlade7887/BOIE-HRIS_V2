<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'document_type' => ['required', 'string', 'max:100'],
            'document_name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'uploaded_at' => ['nullable', 'date'],
        ];
    }
}
