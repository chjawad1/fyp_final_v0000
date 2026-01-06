<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TemplateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by admin middleware/guards
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            // Stricter validation: only allow common document types up to 10MB
            'file'        => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // 10240 KB = 10 MB
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'template file',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A descriptive short name is required to rename the file.',
            'file.required' => 'Please choose a template file to upload.',
            'file.mimes'    => 'Only PDF, DOC, or DOCX files are allowed.',
            'file.max'      => 'The template file must not be larger than 10 MB.',
            'name.max'      => 'The name may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
        ];
    }
}