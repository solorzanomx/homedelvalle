@extends('layouts.portal')
@section('title', 'Mi Cuenta')

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
</div>
@endsection
