<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #f9f9f9; padding: 20px 0; border-bottom: 1px solid #e3e3e0; margin-bottom: 30px; }
        nav { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { font-size: 24px; color: #3B82C4; font-weight: 600; }
        nav-right { display: flex; gap: 15px; align-items: center; }
        nav a, nav button { background: white; color: #3B82C4; padding: 8px 16px; border: 1px solid #e3e3e0; border-radius: 4px; text-decoration: none; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        nav a:hover, nav button:hover { background: #3B82C4; color: white; border-color: #3B82C4; }
        h2 { font-size: 28px; margin-bottom: 30px; color: #1b1b18; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,0.15); }
        .card-image { width: 100%; height: 200px; background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; }
        .card-content { padding: 20px; }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; color: #1b1b18; }
        .card-text { font-size: 14px; color: #706f6c; margin-bottom: 12px; }
        .card-price { font-size: 20px; font-weight: 600; color: #3B82C4; margin-bottom: 15px; }
        .btn { padding: 10px 16px; background: #3B82C4; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.2s; display: inline-block; }
        .btn:hover { background: #1E3A5F; }
        .btn-secondary { background: white; color: #3B82C4; border: 1px solid #3B82C4; }
        .btn-secondary:hover { background: #3B82C4; color: white; }
        .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #999; }
        .empty-state h3 { margin-bottom: 10px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-box { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .stat-number { font-size: 32px; font-weight: 700; color: #3B82C4; }
        .stat-label { font-size: 14px; color: #706f6c; margin-top: 8px; }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>🏠 Inicio</h1>
            <nav-right>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ url('/admin') }}">🔧 Panel Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit">{{ auth()->user()->name }} (Salir)</button>
                </form>
            </nav-right>
        </nav>
    </header>

    <div class="container">
        <h2>📊 Panel de Control</h2>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-number">{{ $properties->count() }}</div>
                <div class="stat-label">Propiedades</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $properties->sum('price') > 0 ? '$' . number_format($properties->avg('price'), 0) : '0' }}</div>
                <div class="stat-label">Precio Promedio</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{ $properties->where('status', 'available')->count() }}</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>

        <h2>🏘️ Propiedades Disponibles</h2>

        @if ($properties->count() > 0)
            <div class="grid">
                @foreach ($properties as $property)
                    <div class="card">
                        <div class="card-image">
                            @if ($property->main_photo)
                                <img src="{{ asset('storage/' . $property->main_photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                🏠
                            @endif
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">{{ $property->title }}</h3>
                            <p class="card-text">{{ substr($property->description, 0, 80) }}...</p>
                            <div class="card-price">${{ number_format($property->price, 0) }}</div>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('properties.show', $property->id) }}" class="btn">Ver Detalles</a>
                                <a href="{{ route('properties.edit', $property->id) }}" class="btn btn-secondary">Editar</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <h3>No hay propiedades registradas</h3>
                <p>Comienza <a href="{{ route('properties.create') }}" style="color: #3B82C4; font-weight: 600;">creando una nueva propiedad</a></p>
            </div>
        @endif

        <div style="margin-top: 40px; text-align: center;">
            <a href="{{ route('properties.create') }}" class="btn" style="padding: 12px 24px; font-size: 16px;">+ Crear Nueva Propiedad</a>
        </div>
    </div>
</body>
</html>
