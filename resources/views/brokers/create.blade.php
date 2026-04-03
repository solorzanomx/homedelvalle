@extends('layouts.app-sidebar')
@section('title', 'Nuevo Broker Externo')

@section('styles')
<style>
.user-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    max-width: 720px; overflow: hidden;
}
.user-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.user-form-header h3 { font-size: 1rem; font-weight: 600; }
.user-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Avatar upload */
.avatar-upload {
    display: flex; align-items: center; gap: 1.25rem; margin-bottom: 0.5rem;
}
.avatar-circle {
    width: 80px; height: 80px; border-radius: 50%; background: var(--border);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    overflow: hidden; cursor: pointer; position: relative;
    font-size: 2rem; color: var(--text-muted); transition: border-color 0.15s;
    border: 2px dashed var(--border);
}
.avatar-circle:hover { border-color: var(--primary); }
.avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
.avatar-circle-overlay {
    position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: none;
    align-items: center; justify-content: center; color: #fff; font-size: 1.1rem;
    border-radius: 50%;
}
.avatar-circle img ~ .avatar-circle-overlay { display: flex; opacity: 0; transition: opacity 0.2s; }
.avatar-circle:hover .avatar-circle-overlay { opacity: 1; }
.avatar-upload-info { font-size: 0.75rem; color: var(--text-muted); }
.avatar-upload-info span { display: block; font-size: 0.82rem; font-weight: 600; color: var(--primary); cursor: pointer; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('brokers.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Brokers Externos</a>
</div>

<div class="user-form-card">
    <div class="user-form-header">
        <h3>Nuevo Broker Externo</h3>
    </div>
    <div class="user-form-body">
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('brokers.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="avatar-upload">
                <div class="avatar-circle" onclick="document.getElementById('avatarFile').click()">
                    <span id="avatarPlaceholder">&#128100;</span>
                    <div class="avatar-circle-overlay">&#128247;</div>
                </div>
                <input type="file" id="avatarFile" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif" style="display:none;" onchange="previewAvatar(this)">
                <div class="avatar-upload-info">
                    <span onclick="document.getElementById('avatarFile').click()">Subir foto</span>
                    JPG, PNG o GIF. Max 2MB.
                </div>
            </div>

            <div class="section-label" style="margin-top:1rem;">Informacion personal</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required autofocus placeholder="Nombre completo">
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+52 55 1234 5678">
                </div>
                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <select name="broker_company_id" class="form-select">
                        <option value="">Sin empresa</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('broker_company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint"><a href="{{ route('broker-companies.create') }}" target="_blank" style="color:var(--primary);">+ Crear nueva empresa</a></p>
                </div>
            </div>

            <div class="section-label">Datos profesionales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Numero de Licencia</label>
                    <input type="text" name="license_number" class="form-input" value="{{ old('license_number') }}" placeholder="Ej: AMPI-12345">
                </div>
                <div class="form-group">
                    <label class="form-label">Comision (%)</label>
                    <input type="number" name="commission_rate" class="form-input" value="{{ old('commission_rate') }}" step="0.01" min="0" max="100" placeholder="Ej: 3.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Especialidad</label>
                    <input type="text" name="specialty" class="form-input" value="{{ old('specialty') }}" placeholder="Residencial, comercial, terrenos...">
                </div>
                <div class="form-group">
                    <label class="form-label">Como lo conocimos</label>
                    <input type="text" name="referral_source" class="form-input" value="{{ old('referral_source') }}" placeholder="Referido, evento, portal...">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="section-label">Bio</div>
            <div class="form-group">
                <textarea name="bio" class="form-textarea" rows="3" maxlength="500" placeholder="Descripcion profesional del broker...">{{ old('bio') }}</textarea>
                <p class="form-hint">Maximo 500 caracteres</p>
            </div>

            <div class="form-actions">
                <a href="{{ route('brokers.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Broker</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var circle = document.querySelector('.avatar-circle');
        var existing = circle.querySelector('img');
        if (existing) {
            existing.src = e.target.result;
        } else {
            var placeholder = document.getElementById('avatarPlaceholder');
            if (placeholder) placeholder.style.display = 'none';
            var img = document.createElement('img');
            img.src = e.target.result;
            circle.insertBefore(img, circle.firstChild);
            circle.querySelector('.avatar-circle-overlay').style.display = 'flex';
        }
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
