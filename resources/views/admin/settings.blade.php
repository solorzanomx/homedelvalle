<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        nav { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { font-size: 24px; }
        nav a, nav button { background: white; color: #667eea; padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; font-weight: 600; margin-left: 10px; }
        nav a:hover, nav button:hover { background: #f0f0f0; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .content { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #e3e3e0; border-radius: 4px; font-family: inherit; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { padding: 12px 24px; background: #16a34a; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px; }
        .btn:hover { background: #15803d; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <h1>⚙️ Configuración del Sitio</h1>
            <div>
                <a href="{{ url('/admin') }}">← Volver</a>
                <span style="margin: 0 10px; color: white;">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            </div>
        </nav>

        @if (session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="content">
            <form method="POST" action="{{ route('admin.settings.update') }}" style="max-width: 600px;">
                @csrf
                
                <div class="form-group">
                    <label for="site_name">Nombre del Sitio</label>
                    <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings->site_name ?? 'Mi Inmobiliaria') }}">
                </div>
                
                <div class="form-group">
                    <label for="site_tagline">Eslogan</label>
                    <input type="text" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settings->site_tagline ?? 'Encuentra tu hogar perfecto') }}">
                </div>
                
                <div class="form-group">
                    <label for="primary_color">Color Primario</label>
                    <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?? '#667eea') }}">
                </div>
                
                <div class="form-group">
                    <label for="secondary_color">Color Secundario</label>
                    <input type="color" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color ?? '#764ba2') }}">
                </div>
                
                <div class="form-group">
                    <label for="home_welcome_text">Texto de Bienvenida</label>
                    <textarea id="home_welcome_text" name="home_welcome_text">{{ old('home_welcome_text', $settings->home_welcome_text ?? 'Bienvenido a nuestro sitio de bienes raíces') }}</textarea>
                </div>
                
                <button type="submit" class="btn">💾 Guardar Cambios</button>
            </form>
        </div>
    </div>
</body>
</html>
