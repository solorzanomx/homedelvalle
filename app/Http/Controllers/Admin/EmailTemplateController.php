<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get();
        return view('admin.email.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'body_text' => 'nullable|string',
        ]);

        EmailTemplate::create($validated);

        return redirect()->route('admin.email.templates.index')->with('success', 'Template creado correctamente.');
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.email.templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name,' . $template->id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'body_text' => 'nullable|string',
        ]);

        $template->update($validated);

        return redirect()->route('admin.email.templates.index')->with('success', 'Template actualizado correctamente.');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();
        return redirect()->route('admin.email.templates.index')->with('success', 'Template eliminado correctamente.');
    }

    public function preview(EmailTemplate $template)
    {
        $rendered = $template->render([
            'Nombre' => 'Juan Perez',
            'Email' => 'juan@ejemplo.com',
            'Password' => '********',
            'Fecha' => now()->format('d/m/Y H:i'),
            'Rol' => 'editor',
            'Sitio' => 'Homedelvalle',
        ]);

        return response($rendered['body']);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:3072',
        ]);

        $path = $request->file('image')->store('email-images', 'public');

        return response()->json([
            'success' => true,
            'url' => Storage::url($path),
        ]);
    }

    /**
     * Send a test email with the template content (works for both saved and unsaved templates).
     */
    public function sendTest(Request $request, EmailService $emailService)
    {
        $request->validate([
            'test_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'body_text' => 'nullable|string',
        ]);

        $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';

        // Sample variables for test
        $sampleVars = [
            'Nombre' => 'Juan',
            'Apellido' => 'Perez',
            'Email' => 'juan@ejemplo.com',
            'Password' => 'Test1234',
            'Fecha' => now()->format('d/m/Y H:i'),
            'Rol' => 'editor',
            'Sitio' => $siteName,
        ];

        $subject = $request->input('subject');
        $body = $request->input('body');
        $bodyText = $request->input('body_text');

        foreach ($sampleVars as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
            if ($bodyText) {
                $bodyText = str_replace($placeholder, $value, $bodyText);
            }
        }

        $sent = $emailService->send(
            $request->input('test_email'),
            '[PRUEBA] ' . $subject,
            $body,
            null,
            $bodyText
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent
                ? 'Correo de prueba enviado a ' . $request->input('test_email')
                : 'No se pudo enviar. Revisa la configuracion SMTP.',
        ]);
    }
}
