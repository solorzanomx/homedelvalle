<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('custom_templates.assign') ?? false;
    }

    public function rules(): array
    {
        return [
            'trigger_type' => 'required|in:event,form_submission,user_action',
            'trigger_name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'trigger_type.required' => 'El tipo de evento es requerido',
            'trigger_name.required' => 'El nombre del evento es requerido',
        ];
    }
}
