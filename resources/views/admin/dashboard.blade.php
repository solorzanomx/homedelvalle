<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Admin - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        nav { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { font-size: 24px; }
        nav a, nav button { background: white; color: #667eea; padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; font-weight: 600; margin-left: 10px; }
        nav a:hover, nav button:hover { background: #f0f0f0; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #e3e3e0; }
        .tabs a, .tabs button { padding: 12px 20px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-size: 14px; font-weight: 500; color: #706f6c; text-decoration: none; }
        .tabs a.active, .tabs button.active { color: #667eea; border-bottom-color: #667eea; }
        .content { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { font-size: 32px; margin-bottom: 8px; }
        .stat-card p { font-size: 14px; opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; border-bottom: 1px solid #e3e3e0; }
        td { padding: 12px; border-bottom: 1px solid #e3e3e0; }
        tr:hover { background: #f9f9f9; }
        .btn { padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; }
        .btn:hover { background: #764ba2; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <h1>🔧 Panel de Administración</h1>
            <div>
                <span style="margin-right: 20px;">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit">Salir</button>
                </form>
            </div>
        </nav>

        <div class="tabs">
            <button class="tab-btn active" data-tab="stats">📊 Dashboard</button>
            <button class="tab-btn" data-tab="brokers">👥 Gestionar Brokers</button>
            <button class="tab-btn" data-tab="settings">⚙️ Configuración</button>
        </div>

        <div class="content">
            <!-- Dashboard Stats -->
            <div id="stats" class="tab-content">
                <h2 style="margin-bottom: 20px;">Estadísticas del Sistema</h2>
                <div class="stats">
                    <div class="stat-card">
                        <h3>{{ $propertiesCount }}</h3>
                        <p>Propiedades</p>
                    </div>
                    <div class="stat-card">
                        <h3>{{ $clientsCount }}</h3>
                        <p>Clientes</p>
                    </div>
                    <div class="stat-card">
                        <h3>{{ $brokersCount }}</h3>
                        <p>Brokers</p>
                    </div>
                    <div class="stat-card">
                        <h3>{{ $usersCount }}</h3>
                        <p>Usuarios</p>
                    </div>
                </div>
            </div>

            <!-- Brokers Management -->
            <div id="brokers" class="tab-content hidden">
                <h2 style="margin-bottom: 20px;">Gestión de Brokers</h2>
                
                <h3 style="margin-top: 20px; margin-bottom: 10px;">Brokers Aprobados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvedBrokers as $broker)
                            <tr>
                                <td>{{ $broker->name }}</td>
                                <td>{{ $broker->email }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.brokers.revoke', $broker->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn" style="background: #dc2626;">Revocar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align: center; padding: 20px; color: #999;">No hay brokers aprobados</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <h3 style="margin-top: 30px; margin-bottom: 10px;">Usuarios Pendientes</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.brokers.approve', $user->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn">Aprobar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.brokers.makeAdmin', $user->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn" style="background: #f59e0b;">Admin</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align: center; padding: 20px; color: #999;">No hay usuarios pendientes</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Settings -->
            <div id="settings" class="tab-content hidden">
                <h2 style="margin-bottom: 20px;">Configuración del Sitio</h2>
                <form method="POST" action="{{ route('admin.settings.update') }}" style="max-width: 600px;">
                    @csrf
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nombre del Sitio</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $settings->site_name ?? '') }}" style="width: 100%; padding: 10px; border: 1px solid #e3e3e0; border-radius: 4px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Eslogan</label>
                        <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings->site_tagline ?? '') }}" style="width: 100%; padding: 10px; border: 1px solid #e3e3e0; border-radius: 4px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Color Primario</label>
                        <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?? '#667eea') }}" style="width: 100%; padding: 5px; border: 1px solid #e3e3e0; border-radius: 4px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Color Secundario</label>
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color ?? '#764ba2') }}" style="width: 100%; padding: 5px; border: 1px solid #e3e3e0; border-radius: 4px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Bienvenida</label>
                        <textarea name="home_welcome_text" style="width: 100%; padding: 10px; border: 1px solid #e3e3e0; border-radius: 4px; min-height: 80px;">{{ old('home_welcome_text', $settings->home_welcome_text ?? '') }}</textarea>
                    </div>
                    
                    <button type="submit" class="btn" style="background: #16a34a; padding: 12px 24px; font-size: 14px;">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.remove('hidden');
            });
        });
    </script>
</body>
</html>
