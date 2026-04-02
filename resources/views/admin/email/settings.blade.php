@extends('layouts.app-sidebar')
@section('title', 'Configuracion de Correo')

@section('content')
<div class="page-header">
    <div>
        <h2>Correo del Sistema</h2>
        <p class="text-muted">Configura el servidor SMTP para enviar correos desde la plataforma</p>
    </div>
    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline">&#9993; Templates</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">
    {{-- SMTP Config --}}
    <div class="card">
        <div class="card-header"><h3>Servidor SMTP</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.email.settings.update') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Servidor SMTP <span class="required">*</span></label>
                        <input type="text" name="smtp_server" class="form-input"
                               value="{{ old('smtp_server', $emailSettings->smtp_server ?? 'smtp.gmail.com') }}" required>
                        @error('smtp_server') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Puerto <span class="required">*</span></label>
                        <input type="number" name="port" class="form-input"
                               value="{{ old('port', $emailSettings->port ?? 587) }}" required>
                        @error('port') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Remitente <span class="required">*</span></label>
                        <input type="email" name="from_email" class="form-input"
                               value="{{ old('from_email', $emailSettings->from_email ?? '') }}" required>
                        @error('from_email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre del Remitente</label>
                        <input type="text" name="from_name" class="form-input"
                               value="{{ old('from_name', $emailSettings->from_name ?? '') }}"
                               placeholder="CRM Homedelvalle">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Contrasena / App Password</label>
                        <input type="password" name="password" class="form-input"
                               placeholder="{{ $emailSettings && $emailSettings->password ? '••••••••  (dejar vacio para no cambiar)' : 'Ingresa la contrasena' }}">
                        <p class="form-hint">Para Gmail, usa una "Contrasena de aplicacion" generada en tu cuenta Google.</p>
                        @error('password') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group full-width">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="hidden" name="enable_ssl" value="0">
                            <input type="checkbox" name="enable_ssl" value="1"
                                   {{ old('enable_ssl', $emailSettings->enable_ssl ?? true) ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:var(--primary);">
                            <span class="form-label" style="margin:0;">Usar SSL/TLS (STARTTLS)</span>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Configuracion</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Test & Status --}}
    <div>
        <div class="card">
            <div class="card-header"><h3>Estado de la Configuracion</h3></div>
            <div class="card-body">
                @if($emailSettings && $emailSettings->from_email && $emailSettings->smtp_server)
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                        <div style="width:10px; height:10px; border-radius:50%; background:var(--success);"></div>
                        <span>SMTP configurado</span>
                    </div>
                    <div style="font-size:0.85rem; color:var(--text-muted); margin-bottom:1rem;">
                        <p><strong>Servidor:</strong> {{ $emailSettings->smtp_server }}:{{ $emailSettings->port }}</p>
                        <p><strong>Remitente:</strong> {{ $emailSettings->from_email }}</p>
                        <p><strong>SSL:</strong> {{ $emailSettings->enable_ssl ? 'Activado' : 'Desactivado' }}</p>
                    </div>
                @else
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                        <div style="width:10px; height:10px; border-radius:50%; background:var(--danger);"></div>
                        <span>SMTP no configurado</span>
                    </div>
                    <p class="text-muted" style="font-size:0.85rem;">Configura los datos SMTP para poder enviar correos.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Probar Conexion</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.email.settings.test-connection') }}" style="margin-bottom:1rem;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width:100%; justify-content:center;">
                        &#9889; Verificar Conexion SMTP
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.email.settings.send-test') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Enviar correo de prueba</label>
                        <div style="display:flex; gap:0.5rem;">
                            <input type="email" name="test_email" class="form-input" placeholder="correo@ejemplo.com" required>
                            <button type="submit" class="btn btn-primary" style="white-space:nowrap;">Enviar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Guia Rapida</h3></div>
            <div class="card-body" style="font-size:0.82rem; color:var(--text-muted);">
                <p style="margin-bottom:0.5rem;"><strong>Gmail:</strong></p>
                <ul style="margin-left:1rem; margin-bottom:0.75rem;">
                    <li>Servidor: smtp.gmail.com</li>
                    <li>Puerto: 587</li>
                    <li>SSL: Activado</li>
                    <li>Usa "App Password" de Google</li>
                </ul>
                <p style="margin-bottom:0.5rem;"><strong>Outlook:</strong></p>
                <ul style="margin-left:1rem;">
                    <li>Servidor: smtp.office365.com</li>
                    <li>Puerto: 587</li>
                    <li>SSL: Activado</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
