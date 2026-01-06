<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;

class RejectProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Keep permissive to avoid accidental 403s; rely on existing middleware/policies.
        return true;
    }

    public function rules(): array
    {
        return [
            // Use "sometimes|nullable" to avoid breaking existing forms that may not send a reason
            'reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' => 'rejection reason',
        ];
    }
}