@extends('layouts.portal')
@section('title', 'Mi Cuenta')

@section('styles')
/* Toggle switch */
.toggle{width:44px;height:24px;background:#D1D5DB;border-radius:12px;transition:background .2s;position:relative;display:inline-block;flex-shrink:0;}
.toggle::after{content:'';position:absolute;top:2px;left:2px;width:20px;height:20px;border-radius:10px;background:#fff;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.toggle.on{background:#0E304B;}
.toggle.on::after{transform:translateX(20px);}
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Mi Cuenta</h2>
        <p class="text-muted">Gestiona tu informacion de acceso</p>
    </div>
</div>

<div style="max-width:500px;">
    {{-- Account Info --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <h3>Informacion</h3>
        </div>
        <div class="card-body">
            <div class="detail-row">
                <span class="label">Nombre</span>
                <span class="value">{{ Auth::user()->name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Email</span>
                <span class="value">{{ Auth::user()->email }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Miembro desde</span>
                <span class="value">{{ Auth::user()->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="card">
        <div class="card-header">
            <h3>Cambiar Contrasena</h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-error" style="margin-bottom:1rem;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('portal.account.password') }}">
                @csrf @method('PUT')
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <div class="form-group">
                        <label class="form-label">Contrasena Actual</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva Contrasena</label>
                        <input type="password" name="password" class="form-input" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar Nueva Contrasena</label>
                        <input type="password" name="password_confirmation" class="form-input" required minlength="6">
                    </div>
                    <div style="margin-top:0.5rem;">
                        <button type="submit" class="btn btn-primary">Actualizar Contrasena</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Notification Preferences --}}
    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header">
            <div>
                <h3>Notificaciones por email</h3>
                <p class="text-muted" style="font-size:.78rem;margin-top:2px;">Elige cuándo quieres recibir correos sobre tu inmueble</p>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('portal.notifications.update') }}" method="POST">
                @csrf @method('PUT')

                <div style="display:flex;flex-direction:column;gap:0;">
                    @foreach([
                        ['key' => 'notify_visit_scheduled',   'label' => 'Nueva visita agendada',               'desc' => 'Cuando un interesado agenda una visita a tu inmueble'],
                        ['key' => 'notify_visit_confirmed',   'label' => 'Visita confirmada por el interesado',  'desc' => 'Cuando el visitante confirma que asistirá'],
                        ['key' => 'notify_visit_rescheduled', 'label' => 'Visita reagendada o cancelada',        'desc' => 'Cuando un visitante solicita cambiar la fecha'],
                        ['key' => 'notify_process_updates',   'label' => 'Avance en mi proceso',                 'desc' => 'Documentos aprobados, cambios de etapa, etc.'],
                    ] as $pref)
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:.75rem 0;border-bottom:1px solid var(--border);">
                        <div>
                            <div style="font-weight:600;font-size:.88rem;">{{ $pref['label'] }}</div>
                            <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px;">{{ $pref['desc'] }}</div>
                        </div>
                        <label style="position:relative;display:inline-flex;align-items:center;cursor:pointer;flex-shrink:0;margin-top:2px;">
                            <input type="checkbox" name="{{ $pref['key'] }}" value="1" style="display:none;"
                                {{ $notifPrefs->{$pref['key']} ? 'checked' : '' }}
                                onchange="this.nextElementSibling.classList.toggle('on', this.checked)">
                            <div class="toggle {{ $notifPrefs->{$pref['key']} ? 'on' : '' }}"></div>
                        </label>
                    </div>
                    @endforeach

                    {{-- Summary frequency --}}
                    <div style="padding:.75rem 0;">
                        <div style="font-weight:600;font-size:.88rem;margin-bottom:.5rem;">Resumen de actividad</div>
                        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                            @foreach(['none' => 'No recibir', 'weekly' => 'Semanal (lunes)', 'monthly' => 'Mensual'] as $val => $lbl)
                            <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.85rem;">
                                <input type="radio" name="summary_frequency" value="{{ $val }}"
                                    {{ $notifPrefs->summary_frequency === $val ? 'checked' : '' }}
                                    style="accent-color:#0E304B;">
                                {{ $lbl }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div style="margin-top:1.25rem;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">Guardar preferencias</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
