@extends('layouts.app-sidebar')
@section('title', 'Editar Broker')

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
.meta-info {
    font-size: 0.78rem; color: var(--text-muted);
    padding: 0.75rem 0; border-top: 1px solid var(--border); margin-top: 1.25rem;
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Broker</h2>
        <p class="text-muted">{{ $broker->name }}</p>
    </div>
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

        <form method="POST" action="{{ route('brokers.update', $broker) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="section-title">Informacion Personal</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $broker->name) }}" required>
                    @error('name') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $broker->email) }}" required>
                    @error('email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $broker->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <input type="text" name="company_name" class="form-input" value="{{ old('company_name', $broker->company_name) }}">
                </div>
            </div>

            <div class="section-title">Datos Profesionales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Numero de Licencia</label>
                    <input type="text" name="license_number" class="form-input" value="{{ old('license_number', $broker->license_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Comision (%)</label>
                    <input type="number" name="commission_rate" class="form-input" value="{{ old('commission_rate', $broker->commission_rate) }}" step="0.01" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $broker->status) === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ old('status', $broker->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="section-title">Foto</div>
            <div class="form-group">
                <div class="photo-upload" onclick="document.getElementById('photoInput').click()">
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                    <div id="photoPreview">
                        @if($broker->photo)
                            <img src="{{ asset('storage/' . $broker->photo) }}" style="max-height:100px; border-radius:50%;">
                            <p class="form-hint">{{ basename($broker->photo) }} — clic para cambiar</p>
                        @else
                            <p class="text-muted" style="margin:0;">Haz clic para seleccionar una foto</p>
                            <p class="form-hint">JPG, PNG, GIF (max 2MB)</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-title">Bio</div>
            <div class="form-group">
                <textarea name="bio" class="form-textarea" rows="4">{{ old('bio', $broker->bio) }}</textarea>
            </div>

            <div class="meta-info">
                Creado: {{ $broker->created_at->format('d/m/Y H:i') }} &middot;
                Actualizado: {{ $broker->updated_at->format('d/m/Y H:i') }}
            </div>

            <div class="form-actions">
                <a href="{{ route('brokers.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
