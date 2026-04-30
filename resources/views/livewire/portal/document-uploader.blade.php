<div id="doc-uploader-{{ $this->getId() }}">
<style>
@keyframes lw-spin { to { transform: rotate(360deg); } }
#doc-uploader-{{ $this->getId() }} .lw-spinner {
    width:22px;height:22px;border:3px solid #bfdbfe;border-top-color:#1D4ED8;
    border-radius:50%;display:inline-block;animation:lw-spin .7s linear infinite;
}
</style>

{{-- ── Mensaje de éxito ─────────────────────────────────────────────────────── --}}
@if($successMsg)
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;gap:.65rem;">
    <div style="display:flex;align-items:center;gap:.5rem;">
        <span style="color:#10b981;">✓</span>
        <span style="font-size:.83rem;font-weight:600;color:#166534;">{{ $successMsg }}</span>
    </div>
    <button wire:click="clearMessages" style="background:none;border:none;cursor:pointer;color:#6ee7b7;font-size:1rem;line-height:1;">&times;</button>
</div>
@endif

{{-- ── Mensaje de error ─────────────────────────────────────────────────────── --}}
@if($errorMsg)
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;gap:.65rem;">
    <div style="display:flex;align-items:center;gap:.5rem;">
        <span style="color:#ef4444;">⚠</span>
        <span style="font-size:.83rem;font-weight:600;color:#991b1b;">{{ $errorMsg }}</span>
    </div>
    <button wire:click="clearMessages" style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:1rem;line-height:1;">&times;</button>
</div>
@endif

{{-- ── Botón abrir formulario ───────────────────────────────────────────────── --}}
@if(!$showForm)
<div style="margin-bottom:1.25rem;">
    <button wire:click="$set('showForm', true)"
            style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:#1D4ED8;color:#fff;font-size:.82rem;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        Subir documento
    </button>
</div>
@endif

{{-- ── Formulario ───────────────────────────────────────────────────────────── --}}
@if($showForm)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:.95rem;color:#0f172a;">Subir nuevo documento</span>
        <button wire:click="$set('showForm', false)"
                style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
    </div>

    <form wire:submit.prevent="upload">

        {{-- Categoría --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.4rem;">
                Categoría *
            </label>
            <select wire:model="category"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;background:#fff;">
                <option value="">Selecciona una categoría...</option>
                @foreach($availableCategories as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
            </select>
            @error('category')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
        </div>

        {{-- Nombre --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.4rem;">
                Nombre del documento *
            </label>
            <input wire:model="label" type="text" placeholder="Ej: INE Frente, Contrato firmado..."
                   style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;">
            @error('label')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
        </div>

        {{-- Archivo --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.4rem;">
                Archivo *
            </label>

            {{-- Zona de drop --}}
            <div id="drop-zone-{{ $this->getId() }}"
                 onclick="document.getElementById('file-inp-{{ $this->getId() }}').click()"
                 style="border:2px dashed #e2e8f0;border-radius:10px;padding:1.75rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;">

                {{-- Cargando --}}
                <div wire:loading wire:target="file" style="padding:.5rem 0;">
                    <div class="lw-spinner" style="margin:0 auto .5rem;"></div>
                    <p style="font-size:.82rem;color:#1D4ED8;">Procesando archivo...</p>
                </div>

                {{-- Contenido normal --}}
                <div wire:loading.remove wire:target="file">
                    @if($file)
                    <div>
                        <div style="width:36px;height:36px;border-radius:9px;background:#f0fdf4;border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;margin:0 auto .6rem;font-size:1.1rem;">📄</div>
                        <p style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $file->getClientOriginalName() }}</p>
                        <p style="font-size:.72rem;color:#64748b;margin-top:.2rem;">{{ round($file->getSize() / 1024) }} KB</p>
                    </div>
                    @else
                    <div>
                        <div style="width:36px;height:36px;border-radius:9px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto .6rem;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                        </div>
                        <p style="font-size:.83rem;color:#64748b;">
                            <strong style="color:#1D4ED8;">Haz clic para seleccionar</strong> o arrastra aquí
                        </p>
                        <p style="font-size:.72rem;color:#94a3b8;margin-top:.25rem;">PDF, JPG, PNG, DOC — máx. 10 MB</p>
                    </div>
                    @endif
                </div>
            </div>

            <input id="file-inp-{{ $this->getId() }}"
                   type="file"
                   wire:model="file"
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                   style="display:none;">
            @error('file')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
        </div>

        {{-- Acciones --}}
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button type="button" wire:click="$set('showForm', false)"
                    style="padding:.5rem 1rem;font-size:.82rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">
                Cancelar
            </button>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="upload"
                    style="padding:.5rem 1.25rem;font-size:.82rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;">
                <div wire:loading wire:target="upload" class="lw-spinner" style="width:14px;height:14px;border-width:2px;border-color:rgba(255,255,255,.4);border-top-color:#fff;"></div>
                <span wire:loading.remove wire:target="upload">Subir documento</span>
                <span wire:loading wire:target="upload">Subiendo...</span>
            </button>
        </div>
    </form>
</div>
@endif

{{-- ── Lista de documentos ──────────────────────────────────────────────────── --}}
@if(count($documents) > 0)
<div style="display:flex;flex-direction:column;gap:.5rem;">
    @foreach($documents as $doc)
    @php
        $sc = match($doc['status']) {
            'verified' => '#10b981',
            'rejected' => '#ef4444',
            'received' => '#3b82f6',
            default    => '#f59e0b',
        };
    @endphp
    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;background:#fff;border:1px solid #e2e8f0;border-radius:9px;">
        <div style="width:32px;height:32px;border-radius:7px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">📄</div>
        <div style="flex:1;min-width:0;">
            <p style="font-weight:600;font-size:.83rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc['label'] }}</p>
            <p style="font-size:.7rem;color:#64748b;margin-top:.1rem;">
                {{ $doc['category'] }}@if($doc['size']) &middot; {{ $doc['size'] }}@endif &middot; {{ $doc['date'] }}
            </p>
        </div>
        <span style="flex-shrink:0;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ $sc }}20;color:{{ $sc }};">
            {{ $doc['statusLabel'] }}
        </span>
        <div style="flex-shrink:0;display:flex;gap:.3rem;">
            <a href="{{ route('portal.documents.download', $doc['id']) }}"
               style="display:inline-flex;align-items:center;gap:.25rem;padding:.3rem .6rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.72rem;font-weight:600;color:#64748b;text-decoration:none;">
                ↓ Descargar
            </a>
            @if($doc['canDelete'])
            <button wire:click="deleteDocument({{ $doc['id'] }})"
                    onclick="return confirm('¿Eliminar este documento?')"
                    style="padding:.3rem .5rem;border:1px solid #fecaca;border-radius:6px;font-size:.72rem;color:#ef4444;background:none;cursor:pointer;">
                ✕
            </button>
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

</div>

<script>
(function() {
    function initDropZone() {
        var zoneId = 'drop-zone-{{ $this->getId() }}';
        var inputId = 'file-inp-{{ $this->getId() }}';
        var zone = document.getElementById(zoneId);
        if (!zone || zone._dropInit) return;
        zone._dropInit = true;

        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            zone.style.borderColor = '#1D4ED8';
            zone.style.background  = '#eff6ff';
        });
        zone.addEventListener('dragleave', function() {
            zone.style.borderColor = '#e2e8f0';
            zone.style.background  = '';
        });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            zone.style.borderColor = '#e2e8f0';
            zone.style.background  = '';
            var input = document.getElementById(inputId);
            if (input && e.dataTransfer.files.length) {
                var dt = new DataTransfer();
                dt.items.add(e.dataTransfer.files[0]);
                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }

    document.addEventListener('livewire:initialized',  initDropZone);
    document.addEventListener('livewire:updated',       initDropZone);
    document.addEventListener('DOMContentLoaded',       initDropZone);
})();
</script>
