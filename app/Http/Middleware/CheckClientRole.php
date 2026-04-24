<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckClientRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'client') {
            return $next($request);
        }

        // Authenticated as non-client (e.g. admin testing the portal link).
        // Log out and redirect to login so they can sign in with the client account.
        if ($user) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('info', 'Esta sección es exclusiva para clientes del portal. Inicia sesión con tu cuenta de cliente.');
        }

        // Not authenticated — let the auth middleware handle the redirect.
        return redirect()->route('login')
            ->with('url.intended', $request->url());
    }
}
