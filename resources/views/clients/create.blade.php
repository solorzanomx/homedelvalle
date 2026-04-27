@extends('layouts.app-sidebar')
@section('title', 'Nuevo Cliente')

@section('styles')
<style>
@import url('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css');

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

.photo-editor-modal {
    position: fixed;
    inset: 0;
    z-index: 1400;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.photo-editor-modal.is-open { display: flex; }

.photo-editor-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(3px);
}

.photo-editor-dialog {
    position: relative;
    width: min(880px, 100%);
    background: #231d1b;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    overflow: hidden;
    color: #f6efe8;
    box-shadow: 0 24px 80px rgba(0,0,0,0.35);
}

.photo-editor-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 280px;
    min-height: 520px;
}

.photo-editor-stage {
    background: #2b2422;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.photo-editor-stage img { max-width: 100%; display: block; }

.photo-editor-side {
    background: #302826;
    border-left: 1px solid rgba(255,255,255,0.08);
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.photo-editor-title { font-size: 0.95rem; font-weight: 700; }
.photo-editor-subtitle { font-size: 0.75rem; color: rgba(246,239,232,0.65); }

.photo-editor-controls {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
}

.photo-editor-controls button {
    border: 1px solid rgba(255,255,255,0.14);
    background: rgba(255,255,255,0.05);
    color: #f6efe8;
    border-radius: 10px;
    min-height: 36px;
    cursor: pointer;
}

.photo-editor-controls button:hover { background: rgba(255,255,255,0.11); }

.photo-editor-actions {
    margin-top: auto;
    display: flex;
    justify-content: flex-end;
    gap: 0.65rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255,255,255,0.08);
}

@media (max-width: 860px) {
    .photo-editor-grid { grid-template-columns: 1fr; }
    .photo-editor-side { border-left: 0; border-top: 1px solid rgba(255,255,255,0.08); }
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Nuevo Cliente</h2></div>
    <a href="{{ route('clients.index') }}" class="btn btn-outline">Volver</a>
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

        <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
            @csrf

            @if(request('from_submission'))
            <div style="background:rgba(102,126,234,0.06); border:1px solid rgba(102,126,234,0.15); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.82rem;">
                &#128279; Creando cliente desde lead entrante. Los datos fueron pre-llenados.
            </div>
            <input type="hidden" name="initial_notes" value="Convertido desde lead entrante (formulario de contacto). {{ request('utm_source') ? 'Fuente: '.request('utm_source').'.' : '' }} {{ request('utm_medium') ? 'Medio: '.request('utm_medium').'.' : '' }} {{ request('utm_campaign') ? 'Campana: '.request('utm_campaign').'.' : '' }}">
            @endif

            <div class="section-title">Informacion Personal</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', request('name')) }}" required>
                    @error('name') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', request('email')) }}" required>
                    @error('email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', request('phone')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="tel" name="whatsapp" class="form-input" value="{{ old('whatsapp') }}" placeholder="55 1234 5678">
                </div>
                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" class="form-input" value="{{ old('city') }}">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Direccion</label>
                    <textarea name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">CURP</label>
                    <input type="text" name="curp" class="form-input" value="{{ old('curp') }}" placeholder="XXXX000000XXXXXXXX" maxlength="18" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" class="form-input" value="{{ old('rfc') }}" placeholder="XXXX000000XXX" maxlength="13" style="text-transform:uppercase;">
                </div>
            </div>

            <div class="section-title">Clasificacion del Lead</div>
            <div class="form-group">
                <label class="form-label">Tipo de Interes</label>
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    @foreach(['compra'=>'Compra', 'venta'=>'Venta', 'renta_propietario'=>'Renta (Propietario)', 'renta_inquilino'=>'Renta (Inquilino)'] as $val => $label)
                    <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.88rem; cursor:pointer;">
                        <input type="checkbox" name="interest_types[]" value="{{ $val }}" {{ in_array($val, old('interest_types', [])) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Temperatura del Lead</label>
                    <div style="display:flex; gap:0.75rem;">
                        @foreach(['frio'=>'Frio', 'tibio'=>'Tibio', 'caliente'=>'Caliente'] as $val => $label)
                        @php $tempColors = ['frio'=>'#94a3b8', 'tibio'=>'#f59e0b', 'caliente'=>'#ef4444']; @endphp
                        <label style="display:flex; align-items:center; gap:0.35rem; font-size:0.85rem; cursor:pointer; padding:0.35rem 0.7rem; border-radius:20px; border:1px solid var(--border); {{ old('lead_temperature') === $val ? 'background:'.$tempColors[$val].'; color:#fff; border-color:'.$tempColors[$val] : '' }}">
                            <input type="radio" name="lead_temperature" value="{{ $val }}" {{ old('lead_temperature') === $val ? 'checked' : '' }} style="display:none;" onchange="updateTempRadios()">
                            <span style="width:8px; height:8px; border-radius:50%; background:{{ $tempColors[$val] }}; flex-shrink:0;"></span>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad</label>
                    <select name="priority" class="form-select">
                        <option value="">Sin definir</option>
                        @foreach(['alta'=>'Alta', 'media'=>'Media', 'baja'=>'Baja'] as $val => $label)
                            <option value="{{ $val }}" {{ old('priority') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-title">Asignacion</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Asignado a</label>
                    <div style="padding:0.55rem 0.8rem; background:var(--bg); border-radius:var(--radius); font-size:0.88rem; color:var(--text);">
                        {{ Auth::user()->name }} {{ Auth::user()->last_name ?? '' }}
                    </div>
                    <input type="hidden" name="assigned_user_id" value="{{ Auth::id() }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Broker (opcional)</label>
                    <select name="broker_id" class="form-select">
                        <option value="">Sin asignar</option>
                        @foreach($brokers as $broker)
                            <option value="{{ $broker->id }}" {{ old('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-title">Preferencias de Busqueda</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Propiedad</label>
                    <select name="property_type" class="form-select">
                        <option value="">Sin preferencia</option>
                        @foreach(['house'=>'Casa','apartment'=>'Departamento','condo'=>'Condominio','land'=>'Terreno'] as $val => $label)
                            <option value="{{ $val }}" {{ old('property_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="display:none;"></div>
                <div class="form-group">
                    <label class="form-label">Presupuesto Minimo</label>
                    <input type="number" name="budget_min" class="form-input" value="{{ old('budget_min') }}" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Presupuesto Maximo</label>
                    <input type="number" name="budget_max" class="form-input" value="{{ old('budget_max') }}" min="0" step="0.01">
                </div>
            </div>

            <div class="section-title">Notas Iniciales</div>
            <div class="form-group">
                <textarea name="initial_notes" class="form-textarea" rows="3" placeholder="Contexto, necesidades especificas, observaciones...">{{ old('initial_notes') }}</textarea>
            </div>

            <div class="section-title">Origen del Lead</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Canal de Origen</label>
                    <select name="marketing_channel_id" class="form-select" id="channelSelect" onchange="filterCampaigns(this.value)">
                        <option value="">Sin especificar</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}" {{ old('marketing_channel_id') == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Campana</label>
                    <select name="marketing_campaign_id" class="form-select" id="campaignSelect">
                        <option value="">Sin especificar</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" data-channel="{{ $campaign->marketing_channel_id }}" {{ old('marketing_campaign_id') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Costo de Adquisicion</label>
                    <input type="number" name="acquisition_cost" class="form-input" value="{{ old('acquisition_cost') }}" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>

            <div class="section-title">Foto</div>
            <div class="form-group">
                <div class="photo-upload" onclick="document.getElementById('photoInput').click()">
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="startPhotoEditing(this)">
                    <div id="photoPreview">
                        <p class="text-muted" style="margin:0;">Haz clic para seleccionar una foto</p>
                        <p class="form-hint">JPG, PNG, GIF, WebP (max 5MB)</p>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('clients.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Cliente</button>
            </div>
        </form>
    </div>
</div>

<div class="photo-editor-modal" id="photoEditorModal" aria-hidden="true">
    <div class="photo-editor-backdrop" onclick="cancelPhotoEditing()"></div>
    <div class="photo-editor-dialog" role="dialog" aria-modal="true">
        <div class="photo-editor-grid">
            <div class="photo-editor-stage">
                <img id="photoEditorImage" alt="Editor de foto" />
            </div>
            <div class="photo-editor-side">
                <div>
                    <div class="photo-editor-title">Editar foto del cliente</div>
                    <div class="photo-editor-subtitle" id="photoEditorFileName">Ajusta antes de guardar</div>
                </div>

                <div class="photo-editor-controls">
                    <button type="button" onclick="zoomCropper(0.1)" title="Zoom +">+</button>
                    <button type="button" onclick="zoomCropper(-0.1)" title="Zoom -">-</button>
                    <button type="button" onclick="rotateCropper(-90)" title="Rotar izq">&#8630;</button>
                    <button type="button" onclick="rotateCropper(90)" title="Rotar der">&#8631;</button>
                    <button type="button" onclick="moveCropper(-15, 0)" title="Mover izq">&#8592;</button>
                    <button type="button" onclick="moveCropper(15, 0)" title="Mover der">&#8594;</button>
                    <button type="button" onclick="moveCropper(0, -15)" title="Mover arriba">&#8593;</button>
                    <button type="button" onclick="moveCropper(0, 15)" title="Mover abajo">&#8595;</button>
                    <button type="button" onclick="resetCropper()" title="Reiniciar" style="grid-column: span 4;">Reiniciar</button>
                </div>

                <div class="photo-editor-actions">
                    <button type="button" class="btn btn-outline" onclick="cancelPhotoEditing()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveEditedPhoto()">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
var photoCropper = null;
var photoObjectUrl = null;

function startPhotoEditing(input) {
    if (!input.files || !input.files[0]) return;

    if (typeof Cropper === 'undefined') {
        renderPhotoPreview(input.files[0]);
        return;
    }

    openPhotoEditor(input.files[0]);
}

function openPhotoEditor(file) {
    var modal = document.getElementById('photoEditorModal');
    var image = document.getElementById('photoEditorImage');
    var fileName = document.getElementById('photoEditorFileName');

    destroyPhotoEditor();

    photoObjectUrl = URL.createObjectURL(file);
    image.src = photoObjectUrl;
    fileName.textContent = file.name;

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    image.onload = function() {
        photoCropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            background: false,
            responsive: true,
        });
    };
}

function destroyPhotoEditor() {
    if (photoCropper) {
        photoCropper.destroy();
        photoCropper = null;
    }

    if (photoObjectUrl) {
        URL.revokeObjectURL(photoObjectUrl);
        photoObjectUrl = null;
    }
}

function cancelPhotoEditing() {
    var modal = document.getElementById('photoEditorModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.getElementById('photoInput').value = '';
    destroyPhotoEditor();
}

function saveEditedPhoto() {
    var input = document.getElementById('photoInput');

    if (!photoCropper || !input.files || !input.files[0]) {
        cancelPhotoEditing();
        return;
    }

    var original = input.files[0];
    var exportType = /image\/(png|webp|jpeg)/.test(original.type) ? original.type : 'image/jpeg';
    var canvas = photoCropper.getCroppedCanvas({
        width: 1000,
        height: 1000,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(function(blob) {
        if (!blob) {
            cancelPhotoEditing();
            return;
        }

        var editedFile = new File([blob], original.name, {
            type: exportType,
            lastModified: Date.now(),
        });

        if (typeof DataTransfer !== 'undefined') {
            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(editedFile);
            input.files = dataTransfer.files;
        }

        renderPhotoPreview(editedFile);

        var modal = document.getElementById('photoEditorModal');
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        destroyPhotoEditor();
    }, exportType, 0.92);
}

function renderPhotoPreview(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('photoPreview').innerHTML = '<img src="' + e.target.result + '" style="max-height:100px; border-radius:50%;"><p class="form-hint" style="margin-top:0.5rem;">' + file.name + '</p>';
    };
    reader.readAsDataURL(file);
}

function zoomCropper(amount) {
    if (!photoCropper) return;
    photoCropper.zoom(amount);
}

function rotateCropper(amount) {
    if (!photoCropper) return;
    photoCropper.rotate(amount);
}

function moveCropper(x, y) {
    if (!photoCropper) return;
    photoCropper.move(x, y);
}

function resetCropper() {
    if (!photoCropper) return;
    photoCropper.reset();
}

function filterCampaigns(channelId) {
    var options = document.querySelectorAll('#campaignSelect option[data-channel]');
    options.forEach(function(opt) {
        opt.style.display = (!channelId || opt.dataset.channel === channelId) ? '' : 'none';
        if (opt.style.display === 'none' && opt.selected) opt.selected = false;
    });
}
function updateTempRadios() {
    var colors = {frio:'#94a3b8', tibio:'#f59e0b', caliente:'#ef4444'};
    document.querySelectorAll('input[name="lead_temperature"]').forEach(function(r) {
        var lbl = r.closest('label');
        if (r.checked) {
            lbl.style.background = colors[r.value]; lbl.style.color = '#fff'; lbl.style.borderColor = colors[r.value];
        } else {
            lbl.style.background = ''; lbl.style.color = ''; lbl.style.borderColor = '';
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    var ch = document.getElementById('channelSelect');
    if (ch && ch.value) filterCampaigns(ch.value);
});

document.addEventListener('keydown', function(e) {
    var modal = document.getElementById('photoEditorModal');
    if (!modal || !modal.classList.contains('is-open')) return;

    if (e.key === 'Escape') {
        cancelPhotoEditing();
    }
});
</script>
@endsection
