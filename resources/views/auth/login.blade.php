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
            background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%);
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
            border-color: #3B82C4;
            box-shadow: 0 0 0 3px rgba(59, 130, 196, 0.12);
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
        .pwd-toggle:hover { color: #3B82C4; }
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
            accent-color: #3B82C4;
            cursor: pointer;
        }
        .forgot-link {
            color: #3B82C4;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%);
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
            box-shadow: 0 8px 25px rgba(59, 130, 196, 0.35);
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
        .login-container { position: relative; z-index: 1; }
    </style>
</head>
<body>
    <div class="login-container">
        @if($siteSettings?->logo)
        <div class="login-logo">
            <img src="{{ Storage::url($siteSettings->logo) }}" alt="{{ config('app.name') }}">
        </div>
        @endif

        <h1 style="display:flex;align-items:center;gap:0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3B82C4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            Bienvenido
        </h1>
        <p class="subtitle">Accede a tu cuenta</p>

        @if (session('success'))
            <div class="alert alert-success">
                <x-icon name="check" class="alert-icon" />
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <x-icon name="info" class="alert-icon" />
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <x-icon name="info" class="alert-icon" />
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
                        <span id="eyeShow">
                            <x-icon name="eye" class="w-5 h-5" />
                        </span>
                        {{-- Eye-off icon (hide) --}}
                        <span id="eyeHide" style="display:none;">
                            <x-icon name="eye-off" class="w-5 h-5" />
                        </span>
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

        <div class="secure-badge">
            <x-icon name="lock" class="w-3 h-3" />
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
