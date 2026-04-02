<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class PublicFormController extends Controller
{
    public function show(string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('public.form', compact('form'));
    }

    public function submit(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Dynamic validation from fields config
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];
            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type'] ?? 'text') {
                case 'email': $fieldRules[] = 'email'; break;
                case 'tel': $fieldRules[] = 'string'; break;
                case 'textarea': $fieldRules[] = 'string|max:5000'; break;
                case 'select': case 'radio':
                    $options = collect($field['options'] ?? [])->pluck('value')->filter()->toArray();
                    if ($options) $fieldRules[] = 'in:' . implode(',', $options);
                    break;
                case 'checkbox': $fieldRules[] = 'in:0,1,on,off,true,false'; break;
                default: $fieldRules[] = 'string|max:500';
            }

            $rules['field_' . $field['name']] = implode('|', $fieldRules);
        }

        $validated = $request->validate($rules);

        // Extract field_ prefix data
        $data = [];
        foreach ($form->fields as $field) {
            $key = 'field_' . $field['name'];
            $data[$field['name']] = $validated[$key] ?? null;
        }

        FormSubmission::create([
            'form_id' => $form->id,
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ]);

        $form->increment('submissions_count');

        return back()->with('success', $form->settings['success_message'] ?? 'Formulario enviado correctamente. Gracias.');
    }
}
