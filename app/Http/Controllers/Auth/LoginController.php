<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'El email es requerido',
            'email.email' => 'Ingresa un email valido',
            'password.required' => 'La contrasena es requerida',
        ]);

        if (Auth::attempt($validated, $request->boolean('remember'))) {
            // Check if user is active
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->with('error', 'Tu cuenta esta desactivada. Contacta a tu asesor.');
            }

            $request->session()->regenerate();

            if (Auth::user()->role === 'client') {
                return redirect()->route('portal.dashboard')->with('success', 'Bienvenido a tu portal!');
            }

            return redirect()->route('admin.dashboard')->with('success', 'Bienvenido de vuelta!');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Credenciales incorrectas');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Sesión cerrada exitosamente');
    }
}
