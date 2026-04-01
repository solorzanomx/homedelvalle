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
            'email.email' => 'Ingresa un email válido',
            'password.required' => 'La contraseña es requerida',
        ]);

        if (Auth::attempt($validated)) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'Bienvenido de vuelta!');
        }

        return back()->with('error', 'Email o contraseña incorrectos');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Sesión cerrada exitosamente');
    }
}
