<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Services\EmailService;
use Illuminate\Http\Request;

class EmailSettingsController extends Controller
{
    public function index()
    {
        $emailSettings = EmailSetting::first();
        return view('admin.email.settings', compact('emailSettings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'smtp_server' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'from_email' => 'required|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'enable_ssl' => 'boolean',
        ]);

        $validated['enable_ssl'] = $request->boolean('enable_ssl');

        $settings = EmailSetting::first();

        if ($settings) {
            // Don't overwrite password if field was left empty
            if (empty($validated['password'])) {
                unset($validated['password']);
            }
            $settings->update($validated);
        } else {
            EmailSetting::create($validated);
        }

        return back()->with('success', 'Configuracion de correo actualizada correctamente.');
    }

    public function test(EmailService $emailService)
    {
        $result = $emailService->testConnection();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function sendTest(Request $request, EmailService $emailService)
    {
        $request->validate(['test_email' => 'required|email']);

        $sent = $emailService->send(
            $request->input('test_email'),
            'Correo de prueba - CRM Homedelvalle',
            '<div style="font-family:Arial,sans-serif;max-width:500px;margin:0 auto;padding:20px;">'
            . '<h2 style="color:#4f46e5;">Correo de Prueba</h2>'
            . '<p>Si recibes este mensaje, la configuracion SMTP funciona correctamente.</p>'
            . '<p style="color:#64748b;font-size:13px;">Enviado desde CRM Homedelvalle el ' . now()->format('d/m/Y H:i') . '</p>'
            . '</div>'
        );

        if ($sent) {
            return back()->with('success', 'Correo de prueba enviado a ' . $request->input('test_email'));
        }

        return back()->with('error', 'No se pudo enviar el correo. Revisa la configuracion SMTP y los logs.');
    }
}
