<div id="doc-up-{{ $this->getId() }}">
<style>@keyframes lw-spin{to{transform:rotate(360deg);}}</style>

{{-- Mensajes --}}
@if($successMsg)
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:.83rem;font-weight:600;color:#166534;">✓ {{ $successMsg }}</span>
    <button wire:click="clearMessages" style="background:none;border:none;cursor:pointer;color:#6ee7b7;font-size:1.1rem;">&times;</button>
</div>
@endif
@if($errorMsg)
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:.83rem;font-weight:600;color:#991b1b;">⚠ {{ $errorMsg }}</span>
    <button wire:click="clearMessages" style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:1.1rem;">&times;</button>
</div>
@endif

{{-- Botón abrir form --}}
@if(!$showForm)
<div style="margin-bottom:1.25rem;">
    <button wire:click="$set('showForm', true)"
            style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:#1D4ED8;color:#fff;font-size:.82rem;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
        ↑ Subir documento
    </button>
</div>
@endif

{{-- Formulario — POST estándar al controlador --}}
@if($showForm)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:.95rem;color:#0f172a;">Subir nuevo documento</span>
        <button wire:click="$set('showForm', false)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
    </div>

    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.65rem 1rem;margin-bottom:1rem;font-size:.83rem;color:#991b1b;">
        ⚠ {{ session('error') }}
    </div>
    @endif

    <form method="POST"
          action="{{ route('portal.documents.upload') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- Contexto de renta --}}
        @if($rentalProcessId)
        <input type="hidden" name="rental_process_id" value="{{ $rentalProcessId }}">
        @endif

        {{-- Categoría --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">
                Categoría *
            </label>
            <select name="category" required
                    style="width:100%;border:1px solid {{ $errors->has('category') ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;background:#fff;">
                <option value="">Selecciona una categoría...</option>
                @foreach($availableCategories as $val => $lbl)
                <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            @error('category')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
        </div>

        {{-- Nombre --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">
                Nombre del documento
            </label>
            <input name="label" type="text"
                   value="{{ old('label') }}"
                   placeholder="Ej: INE Frente, Contrato firmado... (opcional)"
                   style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;">
        </div>

        {{-- Archivo --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">
                Archivo *
            </label>
            <div id="dz-{{ $this->getId() }}"
                 onclick="document.getElementById('fi-{{ $this->getId() }}').click()"
                 style="border:2px dashed {{ $errors->has('file') ? '#ef4444' : '#e2e8f0' }};border-radius:10px;padding:1.75rem;text-align:center;cursor:pointer;transition:border-color .2s;">
                <div id="dz-placeholder-{{ $this->getId() }}">
                    <p style="font-size:.83rem;color:#64748b;">
                        <strong style="color:#1D4ED8;">Haz clic para seleccionar</strong> o arrastra aquí
                    </p>
                    <p style="font-size:.72rem;color:#94a3b8;margin-top:.25rem;">PDF, JPG, PNG, DOC — máx. 10 MB</p>
                </div>
                <div id="dz-preview-{{ $this->getId() }}" style="display:none;">
                    <p id="dz-fname-{{ $this->getId() }}" style="font-size:.85rem;font-weight:600;color:#0f172a;">📄 archivo</p>
                    <p id="dz-fsize-{{ $this->getId() }}" style="font-size:.72rem;color:#64748b;"></p>
                </div>
            </div>
            <input id="fi-{{ $this->getId() }}"
                   name="file"
                   type="file"
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                   style="display:none;"
                   required>
            @error('file')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
        </div>

        {{-- Acciones --}}
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button type="button" wire:click="$set('showForm', false)"
                    style="padding:.5rem 1rem;font-size:.82rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">
                Cancelar
            </button>
            <button type="submit"
                    style="padding:.5rem 1.25rem;font-size:.82rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:8px;cursor:pointer;">
                Subir documento
            </button>
        </div>
    </form>
</div>
@endif

{{-- Lista de documentos --}}
@if(count($documents) > 0)
<div style="display:flex;flex-direction:column;gap:.5rem;">
    @foreach($documents as $doc)
    @php $sc = match($doc['status']) { 'verified'=>'#10b981','rejected'=>'#ef4444','received'=>'#3b82f6',default=>'#f59e0b' }; @endphp
    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;background:#fff;border:1px solid #e2e8f0;border-radius:9px;">
        <div style="width:32px;height:32px;border-radius:7px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;">📄</div>
        <div style="flex:1;min-width:0;">
            <p style="font-weight:600;font-size:.83rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc['label'] }}</p>
            <p style="font-size:.7rem;color:#64748b;">{{ $doc['category'] }}@if($doc['size']) &middot; {{ $doc['size'] }}@endif &middot; {{ $doc['date'] }}</p>
        </div>
        <span style="flex-shrink:0;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ $sc }}20;color:{{ $sc }};">{{ $doc['statusLabel'] }}</span>
        <div style="display:flex;gap:.3rem;flex-shrink:0;">
            <a href="{{ route('portal.documents.download', $doc['id']) }}"
               style="padding:.3rem .6rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.72rem;font-weight:600;color:#64748b;text-decoration:none;">↓ Descargar</a>
            @if($doc['canDelete'])
            <button wire:click="deleteDocument({{ $doc['id'] }})"
                    onclick="return confirm('¿Eliminar este documento?')"
                    style="padding:.3rem .5rem;border:1px solid #fecaca;border-radius:6px;font-size:.72rem;color:#ef4444;background:none;cursor:pointer;">✕</button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@elseif(!$showForm)
<div style="text-align:center;padding:2.5rem 1rem;color:#94a3b8;">
    <div style="font-size:2rem;margin-bottom:.5rem;opacity:.4;">📂</div>
    <p style="font-size:.83rem;">Sin documentos aún. Sube el primero.</p>
</div>
@endif

{{-- JS: preview de archivo seleccionado + drag & drop --}}
<script>
(function(){
    var uid   = '{{ $this->getId() }}';
    var fi    = document.getElementById('fi-'    + uid);
    var dz    = document.getElementById('dz-'    + uid);
    var ph    = document.getElementById('dz-placeholder-' + uid);
    var prev  = document.getElementById('dz-preview-'     + uid);
    var fname = document.getElementById('dz-fname-'       + uid);
    var fsize = document.getElementById('dz-fsize-'       + uid);

    function showPreview(file) {
        if (!file || !ph || !prev) return;
        ph.style.display   = 'none';
        prev.style.display = 'block';
        fname.textContent  = '📄 ' + file.name;
        fsize.textContent  = Math.round(file.size / 1024) + ' KB';
    }

    if (fi) fi.addEventListener('change', function(){ showPreview(this.files[0]); });

    if (dz) {
        dz.addEventListener('dragover',  function(e){ e.preventDefault(); dz.style.borderColor='#1D4ED8'; dz.style.background='#eff6ff'; });
        dz.addEventListener('dragleave', function()  { dz.style.borderColor='#e2e8f0'; dz.style.background=''; });
        dz.addEventListener('drop',      function(e) {
            e.preventDefault(); dz.style.borderColor='#e2e8f0'; dz.style.background='';
            if (fi && e.dataTransfer.files.length) {
                var dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                fi.files = dt.files;
                showPreview(e.dataTransfer.files[0]);
            }
        });
    }
})();
</script>

</div>
