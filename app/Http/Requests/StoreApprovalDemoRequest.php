<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalDemoRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'approvers' => ['required', 'array', 'min:1'],
            'approvers.*' => ['required', 'integer', 'distinct'],
            'idempotency_key' => ['required', 'string', 'max:100'],
            'demo_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
