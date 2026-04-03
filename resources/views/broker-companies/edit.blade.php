@extends('layouts.app-sidebar')
@section('title', 'Editar Empresa')

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
.logo-upload {
    border: 2px dashed var(--border); border-radius: var(--radius);
    padding: 1.5rem; text-align: center; cursor: pointer; transition: border-color 0.2s;
}
.logo-upload:hover { border-color: var(--primary); }
.logo-upload img { max-height: 80px; border-radius: 8px; margin-bottom: 0.5rem; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Editar Empresa</h2></div>
    <a href="{{ route('broker-companies.index') }}" class="btn btn-outline">Volver</a>
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

        <form method="POST" action="{{ route('broker-companies.update', $company) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="section-title" style="margin-top:0;">Datos de la Empresa</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre de la Empresa <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $company->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Persona de Contacto</label>
                    <input type="text" name="contact_name" class="form-input" value="{{ old('contact_name', $company->contact_name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $company->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $company->phone) }}">
                </div>
            </div>

            <div class="section-title">Ubicacion y Web</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="address" class="form-input" value="{{ old('address', $company->address) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" class="form-input" value="{{ old('city', $company->city) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Sitio Web</label>
                    <input type="url" name="website" class="form-input" value="{{ old('website', $company->website) }}" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Activa</option>
                        <option value="inactive" {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>
            </div>

            <div class="section-title">Logo</div>
            <div class="form-group">
                <div class="logo-upload" onclick="document.getElementById('logoInput').click()">
                    <input type="file" id="logoInput" name="logo" accept="image/*" style="display:none" onchange="previewLogo(this)">
                    <div id="logoPreview">
                        @if($company->logo)
                            <img src="{{ asset('storage/' . $company->logo) }}" style="max-height:80px; border-radius:8px;">
                            <p class="form-hint" style="margin-top:0.5rem;">Clic para cambiar</p>
                        @else
                            <p class="text-muted" style="margin:0;">Haz clic para seleccionar logo</p>
                            <p class="form-hint">JPG, PNG, WebP (max 2MB)</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-title">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Notas adicionales...">{{ old('notes', $company->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('broker-companies.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>

        <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--border); font-size:0.72rem; color:var(--text-muted);">
            Creado {{ $company->created_at->format('d/m/Y') }} &middot; Brokers vinculados: {{ $company->brokers_count }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').innerHTML = '<img src="' + e.target.result + '" style="max-height:80px; border-radius:8px;"><p class="form-hint" style="margin-top:0.5rem;">' + input.files[0].name + '</p>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
