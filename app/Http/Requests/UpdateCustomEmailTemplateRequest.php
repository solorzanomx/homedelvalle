<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('custom_templates.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'template_type' => 'required|in:custom,marketing,newsletter,promotional',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:150',
            'html_body' => 'required|string',
            'text_body' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del template es requerido',
            'template_type.required' => 'El tipo de template es requerido',
            'subject.required' => 'El asunto del email es requerido',
            'html_body.required' => 'El cuerpo HTML es requerido',
        ];
    }
}
