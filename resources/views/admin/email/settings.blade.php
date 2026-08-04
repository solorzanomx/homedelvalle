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
                        <label class="form-label">Usuario SMTP</label>
                        <input type="text" name="username" class="form-input"
                               value="{{ old('username', $emailSettings->username ?? '') }}"
                               placeholder="Dejar vacio para usar el correo remitente">
                        <p class="form-hint">Para Resend usa "resend". Para Gmail, usa tu correo completo.</p>
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
                        <p><strong>Usuario:</strong> {{ $emailSettings->username ?: $emailSettings->from_email }}</p>
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
                <p style="margin-bottom:0.5rem;"><strong>Resend:</strong></p>
                <ul style="margin-left:1rem; margin-bottom:0.75rem;">
                    <li>Servidor: smtp.resend.com</li>
                    <li>Puerto: 465</li>
                    <li>Usuario: resend</li>
                    <li>SSL: Activado</li>
                    <li>Password: tu API Key</li>
                </ul>
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

<div class="card" style="margin-top:1.5rem;">
    <div class="card-header"><h3>Revisar Respuestas (IMAP)</h3></div>
    <div class="card-body">
        <p class="text-muted" style="font-size:0.85rem; margin-bottom:1rem;">
            Revisa cada 5 minutos la bandeja de entrada del correo remitente para detectar respuestas de clientes,
            registrarlas en su ficha y detener automaticamente cualquier cadencia de seguimiento activa.
            Usa una <strong>"Contrasena de aplicacion"</strong> generada en la cuenta de Google del correo remitente
            (myaccount.google.com/apppasswords) — no la contrasena normal de la cuenta.
        </p>
        <form method="POST" action="{{ route('admin.email.settings.update') }}">
            @csrf
            {{-- Preserve the SMTP fields already saved so this form doesn't blank them out --}}
            <input type="hidden" name="smtp_server" value="{{ $emailSettings->smtp_server ?? '' }}">
            <input type="hidden" name="port" value="{{ $emailSettings->port ?? 587 }}">
            <input type="hidden" name="from_email" value="{{ $emailSettings->from_email ?? '' }}">
            <input type="hidden" name="from_name" value="{{ $emailSettings->from_name ?? '' }}">
            <input type="hidden" name="username" value="{{ $emailSettings->username ?? '' }}">
            <input type="hidden" name="enable_ssl" value="{{ $emailSettings->enable_ssl ?? true ? '1' : '0' }}">

            <div class="form-grid">
                <div class="form-group full-width">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <input type="hidden" name="imap_enabled" value="0">
                        <input type="checkbox" name="imap_enabled" value="1"
                               {{ old('imap_enabled', $emailSettings->imap_enabled ?? false) ? 'checked' : '' }}
                               style="width:16px; height:16px; accent-color:var(--primary);">
                        <span class="form-label" style="margin:0;">Activar revision automatica de respuestas</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Servidor IMAP</label>
                    <input type="text" name="imap_host" class="form-input"
                           value="{{ old('imap_host', $emailSettings->imap_host ?? 'imap.gmail.com') }}"
                           placeholder="imap.gmail.com">
                    @error('imap_host') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Puerto IMAP</label>
                    <input type="number" name="imap_port" class="form-input"
                           value="{{ old('imap_port', $emailSettings->imap_port ?? 993) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Correo a revisar</label>
                    <input type="email" name="imap_username" class="form-input"
                           value="{{ old('imap_username', $emailSettings->imap_username ?? '') }}"
                           placeholder="contacto@homedelvalle.mx">
                    <p class="form-hint">La bandeja real de Google Workspace donde llegan las respuestas.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Contrasena de aplicacion</label>
                    <input type="password" name="imap_password" class="form-input"
                           placeholder="{{ $emailSettings && $emailSettings->imap_password ? '••••••••  (dejar vacio para no cambiar)' : 'Generada en myaccount.google.com/apppasswords' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Encriptacion</label>
                    <select name="imap_encryption" class="form-input">
                        @foreach(['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'Ninguna'] as $val => $label)
                        <option value="{{ $val }}" {{ old('imap_encryption', $emailSettings->imap_encryption ?? 'ssl') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-actions" style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary">Guardar Configuracion IMAP</button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.email.settings.test-imap') }}" style="margin-top:0.75rem;">
            @csrf
            <button type="submit" class="btn btn-outline">&#9889; Verificar Conexion IMAP</button>
        </form>
        @if($emailSettings && $emailSettings->imap_last_checked_at)
        <p class="text-muted" style="font-size:0.8rem; margin-top:0.75rem;">Ultima revision: {{ $emailSettings->imap_last_checked_at->diffForHumans() }}</p>
        @endif
    </div>
</div>
@endsection
