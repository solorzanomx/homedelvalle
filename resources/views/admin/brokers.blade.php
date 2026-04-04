<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionar Brokers - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        nav { background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        nav h1 { font-size: 24px; }
        nav a, nav button { background: white; color: #3B82C4; padding: 8px 16px; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; font-weight: 600; margin-left: 10px; }
        nav a:hover, nav button:hover { background: #f0f0f0; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .content { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { font-size: 20px; margin-bottom: 20px; }
        h3 { font-size: 16px; margin-top: 30px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; border-bottom: 1px solid #e3e3e0; }
        td { padding: 12px; border-bottom: 1px solid #e3e3e0; }
        tr:hover { background: #f9f9f9; }
        .btn { padding: 8px 16px; background: #3B82C4; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; margin-right: 5px; display: inline-block; }
        .btn:hover { background: #1E3A5F; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-warning { background: #f59e0b; }
        .btn-warning:hover { background: #d97706; }
        a { color: #3B82C4; text-decoration: none; }
        a:hover { text-decoration: underline; }
        form { display: inline; }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <h1>👥 Gestión de Brokers</h1>
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

        <div class="content">
            <h2>Gestionar Brokers y Usuarios</h2>

            <h3>✅ Brokers Aprobados ({{ $approvedBrokers->count() }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th style="text-align: right; padding-right: 20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedBrokers as $broker)
                        <tr>
                            <td>{{ $broker->name }}</td>
                            <td>{{ $broker->email }}</td>
                            <td style="text-align: right;">
                                <form method="POST" action="{{ route('admin.brokers.revoke', $broker->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Revocar acceso?')">Revocar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: #999;">No hay brokers aprobados</td></tr>
                    @endforelse
                </tbody>
            </table>

            <h3>⏳ Usuarios Pendientes ({{ $pendingUsers->count() }})</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th style="text-align: right; padding-right: 20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td style="text-align: right;">
                                <form method="POST" action="{{ route('admin.brokers.approve', $user->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn">Aprobar Broker</button>
                                </form>
                                <form method="POST" action="{{ route('admin.brokers.makeAdmin', $user->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('¿Convertir a admin?')">Hacer Admin</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: #999;">No hay usuarios pendientes</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
