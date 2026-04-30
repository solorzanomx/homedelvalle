<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClientPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function __construct(private readonly ClientPortalService $portal) {}

    // ── Login ────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        // Si ya está autenticado como cliente, ir al dashboard
        if (auth()->check() && auth()->user()?->role === 'client') {
            return redirect()->route('portal.dashboard');
        }
        // Si está autenticado pero NO es cliente (admin), cerrar sesión primero
        if (auth()->check()) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
        return view('portal.auth.login');
    }

    public function showRecover()
    {
        if (auth()->check() && auth()->user()?->role === 'client') {
            return redirect()->route('portal.dashboard');
        }
        return view('portal.auth.recover');
    }

    public function showReset(string $token)
    {
        return view('portal.auth.reset', ['token' => $token]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput(['email' => $request->email])
                ->withErrors(['email' => 'Credenciales incorrectas. Verifica tu correo y contraseña.']);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo usuarios con rol 'client' pueden acceder al portal
        if ($user->role !== 'client') {
            Auth::logout();
            return back()->withErrors(['email' => 'Esta cuenta no tiene acceso al portal de clientes.']);
        }

        $request->session()->regenerate();

        // Registrar acceso en audit log
        $this->portal->logAudit($user, 'login', null, null, $request);

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $this->portal->logAudit($user, 'logout', null, null, $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }

    // ── Recuperar contraseña ─────────────────────────────────────────────────

    public function recover(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Te enviamos un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada.');
        }

        return back()->withErrors(['email' => 'No encontramos una cuenta con ese correo.']);
    }

    // ── Restablecer contraseña ───────────────────────────────────────────────

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                Auth::login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('portal.dashboard')
                ->with('status', 'Contraseña actualizada. ¡Bienvenido!');
        }

        return back()->withErrors(['email' => 'El enlace es inválido o ha expirado. Solicita uno nuevo.']);
    }

    // ── Aceptar invitación ───────────────────────────────────────────────────

    public function showAcceptInvitation(string $token)
    {
        $user = $this->portal->getUserByInvitationToken($token);

        if (! $user) {
            return view('portal.auth.accept-invitation', ['valid' => false, 'token' => $token]);
        }

        return view('portal.auth.accept-invitation', [
            'valid' => true,
            'token' => $token,
            'email' => $user->email,
            'name'  => $user->name,
        ]);
    }

    public function acceptInvitation(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = $this->portal->acceptInvitation($request->token, $request->password);

        if (! $user) {
            return back()->withErrors(['token' => 'El enlace de activación es inválido o ha expirado.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $this->portal->logAudit($user, 'invitation_accepted', null, null, $request);

        return redirect()->route('portal.dashboard')
            ->with('status', '¡Bienvenido! Tu cuenta está activa.');
    }
}
