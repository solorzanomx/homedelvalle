@extends('layouts.app-sidebar')
@section('title', 'Nuevo Broker')

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
.photo-upload {
    border: 2px dashed var(--border); border-radius: var(--radius);
    padding: 1.5rem; text-align: center; cursor: pointer; transition: border-color 0.2s;
}
.photo-upload:hover { border-color: var(--primary); }
.photo-upload img { max-height: 100px; border-radius: 50%; margin-bottom: 0.5rem; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Nuevo Broker</h2></div>
    <a href="{{ route('brokers.index') }}" class="btn btn-outline">Volver</a>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
                @foreach($errors->all() as $error)
                    <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('brokers.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="section-title" style="margin-top:0;">Informacion Personal</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone') }}">
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

            <div class="section-title">Datos Profesionales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Numero de Licencia</label>
                    <input type="text" name="license_number" class="form-input" value="{{ old('license_number') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Comision (%)</label>
                    <input type="number" name="commission_rate" class="form-input" value="{{ old('commission_rate') }}" step="0.01" min="0" max="100">
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

            <div class="section-title">Foto</div>
            <div class="form-group">
                <div class="photo-upload" onclick="document.getElementById('photoInput').click()">
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                    <div id="photoPreview">
                        <p class="text-muted" style="margin:0;">Haz clic para seleccionar una foto</p>
                        <p class="form-hint">JPG, PNG, GIF (max 2MB)</p>
                    </div>
                </div>
            </div>

            <div class="section-title">Bio</div>
            <div class="form-group">
                <textarea name="bio" class="form-textarea" rows="4" placeholder="Descripcion profesional del broker...">{{ old('bio') }}</textarea>
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
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').innerHTML = '<img src="' + e.target.result + '" style="max-height:100px; border-radius:50%;"><p class="form-hint" style="margin-top:0.5rem;">' + input.files[0].name + '</p>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
