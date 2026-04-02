<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El email es requerido.',
            'email.email' => 'Ingresa un email valido.',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $resetToken = PasswordResetToken::createForEmail($user->email);

            $resetLink = url('/reset-password?token=' . $resetToken->token);
            $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';

            try {
                $emailService = new EmailService();
                $emailService->sendTemplate('RecuperarPassword', $user->email, [
                    'Nombre' => $user->name,
                    'EnlaceReset' => $resetLink,
                    'Expiracion' => '30',
                    'Fecha' => now()->format('d/m/Y H:i'),
                    'Sitio' => $siteName,
                ], $user->name);
            } catch (\Exception $e) {
                // Silenciar error para no revelar si el email existe
            }
        }

        // Siempre mostrar mensaje generico
        return back()->with('success', 'Si tu email esta registrado, recibiras un enlace para restablecer tu contrasena.');
    }
}
