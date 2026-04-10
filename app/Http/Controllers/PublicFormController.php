<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Http\Request;

class PublicFormController extends Controller
{
    public function show(string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('public.form', compact('form'));
    }

    public function submit(Request $request, string $slug, SpamProtectionService $spam, AutomationEngine $engine)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Honeypot check
        if ($request->filled('website_url')) {
            return back()->with('success', $form->settings['success_message'] ?? 'Formulario enviado correctamente. Gracias.');
        }

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
                case 'email': $fieldRules[] = 'email:rfc,dns'; break;
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
        $textContent = '';
        foreach ($form->fields as $field) {
            $key = 'field_' . $field['name'];
            $value = $validated[$key] ?? null;
            $data[$field['name']] = $value;

            // Collect text fields for spam analysis
            if ($value && in_array($field['type'] ?? 'text', ['text', 'textarea', 'email'])) {
                $textContent .= ' ' . $value;
            }
        }

        // Spam protection
        $spamData = ['message' => trim($textContent), 'email' => $data['email'] ?? $data['correo'] ?? ''];
        $spamCheck = $spam->check(
            $spamData,
            $request->input('recaptcha_token'),
            $request->ip(),
            'form'
        );

        if (! $spamCheck['pass']) {
            return back()->with('success', $form->settings['success_message'] ?? 'Formulario enviado correctamente. Gracias.');
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

        // Trigger automation engine — enroll lead
        $engine->processFormSubmitted([
            'name' => $data['nombre'] ?? $data['name'] ?? 'Lead formulario',
            'email' => $data['email'] ?? $data['correo'] ?? null,
            'phone' => $data['telefono'] ?? $data['phone'] ?? null,
            'message' => $data['mensaje'] ?? $data['message'] ?? '',
            'utm_source' => $request->input('utm_source'),
            'utm_medium' => $request->input('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign'),
        ], 'form');

        return back()->with('success', $form->settings['success_message'] ?? 'Formulario enviado correctamente. Gracias.');
    }
}
