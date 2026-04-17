<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:300',
            'bio' => 'nullable|string|max:200',
            'title' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:150',
            'language' => 'nullable|string|in:es,en,fr,pt',
            'timezone' => 'nullable|string|max:50',
            'email_signature' => 'nullable|string|max:5000',
            'show_phone_on_properties' => 'boolean',
            'shared_card_type' => 'nullable|string|in:micrositio,ficha_simple,sitio_web',
        ]);

        $validated['show_phone_on_properties'] = $request->boolean('show_phone_on_properties');

        $user->update($validated);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $user = Auth::user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'avatar_url' => Storage::disk('public')->url($path),
            ]);
        }

        return back()->with('success', 'Foto de perfil actualizada.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'La contrasena actual es incorrecta.');
        }

        $user->update(['password' => $request->new_password]);

        // Enviar email de confirmacion
        try {
            $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';
            $emailService = new \App\Services\EmailService();
            $emailService->sendTemplate('PasswordCambiado', $user->email, [
                'Nombre' => $user->name,
                'Fecha' => now()->format('d/m/Y H:i'),
                'Sitio' => $siteName,
            ], $user->name);
        } catch (\Exception $e) {
            // No bloquear el flujo si falla el email
        }

        return back()->with('success', 'Contrasena actualizada correctamente.');
    }

    public function updateMailSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'from_email' => 'required|email|max:255',
            'password' => 'nullable|string|max:500',
            'from_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            // Advanced (optional — inherits from global if empty)
            'smtp_server' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl,none',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['encryption'] = $validated['encryption'] ?? 'tls';
        $validated['port'] = $validated['port'] ?? 587;

        // Username defaults to from_email only if explicitly provided
        // If empty, EmailService will inherit from global config
        if (empty($validated['username'])) {
            unset($validated['username']);
        }

        // Don't overwrite password if left empty
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->mailSetting()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('success', 'Correo de empresa configurado correctamente.');
    }

    public function testMailConnection()
    {
        $result = app(\App\Services\EmailService::class)->testConnection(Auth::user());

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
