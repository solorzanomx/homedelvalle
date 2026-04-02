<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')
                ->with('error', 'Enlace de recuperacion invalido.');
        }

        $resetToken = PasswordResetToken::findValidToken($token);

        if (!$resetToken) {
            return redirect()->route('password.forgot')
                ->with('error', 'El enlace ha expirado o ya fue utilizado. Solicita uno nuevo.');
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'La contrasena es requerida.',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
        ]);

        $resetToken = PasswordResetToken::findValidToken($request->token);

        if (!$resetToken) {
            return redirect()->route('password.forgot')
                ->with('error', 'El enlace ha expirado o ya fue utilizado. Solicita uno nuevo.');
        }

        $user = User::where('email', $resetToken->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'No se encontro la cuenta asociada.');
        }

        // Actualizar contrasena (el cast 'hashed' en User hashea automaticamente)
        $user->update(['password' => $request->password]);

        // Invalidar token (un solo uso)
        $resetToken->markAsUsed();

        // Enviar email de confirmacion
        try {
            $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';
            $emailService = new EmailService();
            $emailService->sendTemplate('PasswordCambiado', $user->email, [
                'Nombre' => $user->name,
                'Fecha' => now()->format('d/m/Y H:i'),
                'Sitio' => $siteName,
            ], $user->name);
        } catch (\Exception $e) {
            // No bloquear el flujo si falla el email
        }

        return redirect()->route('login')
            ->with('success', 'Tu contrasena ha sido actualizada. Ya puedes iniciar sesion.');
    }
}
