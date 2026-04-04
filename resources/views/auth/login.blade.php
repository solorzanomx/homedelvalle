<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .login-logo img { height: 40px; }
        .login-container h1 {
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
            color: #1b1b18;
        }
        .login-container .subtitle {
            color: #706f6c;
            margin-bottom: 1.75rem;
            font-size: 0.875rem;
        }
        .form-group { margin-bottom: 1.15rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: #1b1b18;
            font-size: 0.875rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.7rem 0.85rem;
            border: 1.5px solid #e3e3e0;
            border-radius: 10px;
            font-size: 0.875rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
            color: #1b1b18;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
            background: #fff;
        }
        .form-group input.error { border-color: #dc2626; }

        /* Password field wrapper */
        .pwd-wrap {
            position: relative;
        }
        .pwd-wrap input {
            padding-right: 2.75rem;
        }
        .pwd-toggle {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            color: #9ca3af;
            transition: color 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pwd-toggle:hover { color: #667eea; }
        .pwd-toggle svg { width: 20px; height: 20px; }

        /* Alert */
        .alert {
            padding: 0.7rem 0.85rem;
            border-radius: 10px;
            margin-bottom: 1.15rem;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-icon { flex-shrink: 0; width: 18px; height: 18px; }
        .error-message { color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; }

        /* Remember + Forgot */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            font-size: 0.82rem;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            color: #4b5563;
            font-weight: 500;
        }
        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #667eea;
            cursor: pointer;
        }
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 48px;
        }
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
        }
        .submit-btn:active:not(:disabled) {
            transform: translateY(0);
        }
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .btn-spinner {
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }
        .submit-btn.loading .btn-spinner { display: block; }
        .submit-btn.loading .btn-text { display: none; }
        .submit-btn.loading .btn-loading-text { display: inline; }
        .btn-loading-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Register link */
        .register-link {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.82rem;
            color: #706f6c;
        }
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover { text-decoration: underline; }

        /* Secure badge */
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: #9ca3af;
        }
        .secure-badge svg { width: 12px; height: 12px; }
    </style>
</head>
<body>
    <div class="login-container">
        @if($siteSettings?->logo)
        <div class="login-logo">
            <img src="{{ Storage::url($siteSettings->logo) }}" alt="{{ config('app.name') }}">
        </div>
        @endif

        <h1>Bienvenido</h1>
        <p class="subtitle">Inicia sesion en el Office</p>

        @if (session('success'))
            <div class="alert alert-success">
                <svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <svg class="alert-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="@error('email') error @enderror" placeholder="tu@email.com">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contrasena</label>
                <div class="pwd-wrap">
                    <input type="password" id="password" name="password" required autocomplete="current-password" class="@error('password') error @enderror" placeholder="Tu contrasena">
                    <button type="button" class="pwd-toggle" id="pwdToggle" aria-label="Mostrar contrasena" tabindex="-1">
                        {{-- Eye icon (show) --}}
                        <svg id="eyeShow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{-- Eye-off icon (hide) --}}
                        <svg id="eyeHide" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember" value="1"> Recordarme
                </label>
                <a href="{{ route('password.forgot') }}" class="forgot-link">Olvide mi contrasena</a>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <span class="btn-text">Iniciar Sesion</span>
                <span class="btn-loading-text">Iniciando...</span>
                <span class="btn-spinner"></span>
            </button>
        </form>

        <div class="register-link">
            No tienes cuenta? <a href="{{ route('register') }}">Registrate aqui</a>
        </div>

        <div class="secure-badge">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Conexion segura y cifrada
        </div>
    </div>

    <script>
    // Password toggle
    (function() {
        var toggle = document.getElementById('pwdToggle');
        var input = document.getElementById('password');
        var eyeShow = document.getElementById('eyeShow');
        var eyeHide = document.getElementById('eyeHide');

        toggle.addEventListener('click', function() {
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            eyeShow.style.display = isPassword ? 'none' : 'block';
            eyeHide.style.display = isPassword ? 'block' : 'none';
            toggle.setAttribute('aria-label', isPassword ? 'Ocultar contrasena' : 'Mostrar contrasena');
        });
    })();

    // Submit loader
    (function() {
        var form = document.getElementById('loginForm');
        var btn = document.getElementById('submitBtn');

        form.addEventListener('submit', function() {
            btn.classList.add('loading');
            btn.disabled = true;
        });
    })();
    </script>
</body>
</html>
