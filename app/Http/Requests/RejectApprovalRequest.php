<?php

namespace App\Http\Requests;

class RejectApprovalRequest extends ApprovalActionRequest
{
    public function rules(): array
    {
        return [
            'remarks' => ['required', 'string', 'max:5000'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }
}
