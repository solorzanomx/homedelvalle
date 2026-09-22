<div id="doc-up-{{ $this->getId() }}" wire:loading.class="lw-busy" wire:target="upload,file">
<style>
@keyframes lw-spin{to{transform:rotate(360deg);}}
#doc-up-{{ $this->getId() }}.lw-busy { opacity:.7; pointer-events:none; }
.lw-spinner{display:inline-block;width:14px;height:14px;border:2px solid #c7d2fe;border-top-color:#1D4ED8;border-radius:50%;animation:lw-spin .7s linear infinite;}
</style>

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
@error('file') <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:9px;padding:.65rem 1rem;margin-bottom:1rem;font-size:.82rem;color:#991b1b;">⚠ {{ $message }}</div> @enderror

{{-- ═══════════ MODO CASILLA ÚNICA (una sola categoría) ═══════════ --}}
@if($this->isSingleSlot())
    @php $existing = collect($documents)->first(); @endphp
    @if($existing)
        {{-- Ya hay un documento subido: mostrar miniatura/estado --}}
        <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;background:#fff;border:1px solid #e2e8f0;border-radius:9px;">
            @if($existing['thumbUrl'])
                <img src="{{ $existing['thumbUrl'] }}" style="width:44px;height:44px;object-fit:cover;border-radius:7px;border:1px solid #e2e8f0;flex-shrink:0;">
            @else
                <div style="width:44px;height:44px;border-radius:7px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">📄</div>
            @endif
            <div style="flex:1;min-width:0;">
                <p style="font-weight:600;font-size:.83rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $existing['label'] }}</p>
                <p style="font-size:.7rem;color:#64748b;">{{ $existing['size'] }} &middot; {{ $existing['date'] }}</p>
                @if($existing['aiStatus'])
                @php $aiColor = match($existing['aiStatus']) { 'match'=>'#10b981', 'mismatch'=>'#ef4444', 'expired'=>'#ef4444', default=>'#94a3b8' }; @endphp
                <p style="font-size:.68rem;color:{{ $aiColor }};margin-top:.15rem;" title="{{ $existing['aiNotes'] }}">
                    🤖 {{ $existing['aiStatusLabel'] }}
                </p>
                @endif
            </div>
            <span style="flex-shrink:0;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ match($existing['status']){'verified'=>'#10b98120','rejected'=>'#ef444420','received'=>'#3b82f620',default=>'#f59e0b20'} }};color:{{ match($existing['status']){'verified'=>'#10b981','rejected'=>'#ef4444','received'=>'#3b82f6',default=>'#f59e0b'} }};">{{ $existing['statusLabel'] }}</span>
            <div style="display:flex;gap:.3rem;flex-shrink:0;">
                <a href="{{ route('portal.documents.download', $existing['id']) }}" style="padding:.3rem .6rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.72rem;font-weight:600;color:#64748b;text-decoration:none;">↓</a>
                @if($existing['canDelete'])
                <button wire:click="deleteDocument({{ $existing['id'] }})" onclick="return confirm('¿Eliminar y volver a subir?')" style="padding:.3rem .5rem;border:1px solid #fecaca;border-radius:6px;font-size:.72rem;color:#ef4444;background:none;cursor:pointer;">✕</button>
                @endif
            </div>
        </div>
    @else
        {{-- Sin documento: dropzone directo, sube al soltar/seleccionar --}}
        <div>
            <label style="display:block;">
                <div id="slot-dz-{{ $this->getId() }}"
                     style="border:2px dashed #e2e8f0;border-radius:10px;padding:.9rem 1rem;text-align:center;cursor:pointer;transition:border-color .2s;">
                    <span wire:loading.remove wire:target="file" style="font-size:.8rem;color:#64748b;">
                        <strong style="color:#1D4ED8;">Subir archivo</strong> — PDF, JPG o PNG
                    </span>
                    <span wire:loading wire:target="file" style="font-size:.8rem;color:#1D4ED8;display:inline-flex;align-items:center;gap:.4rem;">
                        <span class="lw-spinner"></span> Subiendo...
                    </span>
                </div>
                <input type="file" wire:model="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
            </label>
        </div>
    @endif
@else
{{-- ═══════════ MODO LISTA (varias categorías, como antes) ═══════════ --}}

{{-- Botón abrir form --}}
@if(!$showForm)
<div style="margin-bottom:1.25rem;">
    <button wire:click="$set('showForm', true)"
            style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:#1D4ED8;color:#fff;font-size:.82rem;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
        ↑ Subir documento
    </button>
</div>
@endif

{{-- Formulario — sube vía Livewire, sin recarga --}}
@if($showForm)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:.95rem;color:#0f172a;">Subir nuevo documento</span>
        <button wire:click="$set('showForm', false)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
    </div>

    <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">Categoría *</label>
        <select wire:model="category" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;background:#fff;">
            <option value="">Selecciona una categoría...</option>
            @foreach($availableCategories as $val => $lbl)
            <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>
    </div>

    <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">Nombre del documento</label>
        <input wire:model="label" type="text" placeholder="Ej: INE Frente, Contrato firmado... (opcional)"
               style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;">
    </div>

    <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">Archivo *</label>
        <label style="display:block;">
            <div style="border:2px dashed #e2e8f0;border-radius:10px;padding:1.75rem;text-align:center;cursor:pointer;">
                <span wire:loading.remove wire:target="file">
                    @if($file)
                        <p style="font-size:.85rem;font-weight:600;color:#0f172a;">📄 {{ $file->getClientOriginalName() }}</p>
                    @else
                        <p style="font-size:.83rem;color:#64748b;"><strong style="color:#1D4ED8;">Haz clic para seleccionar</strong></p>
                        <p style="font-size:.72rem;color:#94a3b8;margin-top:.25rem;">PDF, JPG, PNG, DOC — máx. 10 MB</p>
                    @endif
                </span>
                <span wire:loading wire:target="file" style="font-size:.83rem;color:#1D4ED8;display:inline-flex;align-items:center;gap:.4rem;">
                    <span class="lw-spinner"></span> Cargando...
                </span>
            </div>
            <input type="file" wire:model="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;">
        </label>
    </div>

    <div style="display:flex;gap:.75rem;justify-content:flex-end;">
        <button type="button" wire:click="$set('showForm', false)"
                style="padding:.5rem 1rem;font-size:.82rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">
            Cancelar
        </button>
        <button wire:click="upload" wire:loading.attr="disabled" wire:target="upload"
                style="padding:.5rem 1.25rem;font-size:.82rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:8px;cursor:pointer;">
            <span wire:loading.remove wire:target="upload">Subir documento</span>
            <span wire:loading wire:target="upload">Subiendo...</span>
        </button>
    </div>
</div>
@endif

{{-- Lista de documentos --}}
@if(count($documents) > 0)
<div style="display:flex;flex-direction:column;gap:.5rem;">
    @foreach($documents as $doc)
    @php $sc = match($doc['status']) { 'verified'=>'#10b981','rejected'=>'#ef4444','received'=>'#3b82f6',default=>'#f59e0b' }; @endphp
    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;background:#fff;border:1px solid #e2e8f0;border-radius:9px;">
        @if($doc['thumbUrl'])
            <img src="{{ $doc['thumbUrl'] }}" style="width:32px;height:32px;object-fit:cover;border-radius:7px;border:1px solid #e2e8f0;flex-shrink:0;">
        @else
            <div style="width:32px;height:32px;border-radius:7px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;">📄</div>
        @endif
        <div style="flex:1;min-width:0;">
            <p style="font-weight:600;font-size:.83rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc['label'] }}</p>
            <p style="font-size:.7rem;color:#64748b;">{{ $doc['category'] }}@if($doc['size']) &middot; {{ $doc['size'] }}@endif &middot; {{ $doc['date'] }}</p>
            @if($doc['aiStatus'])
            @php $aiColor = match($doc['aiStatus']) { 'match'=>'#10b981', 'mismatch'=>'#ef4444', 'expired'=>'#ef4444', default=>'#94a3b8' }; @endphp
            <p style="font-size:.68rem;color:{{ $aiColor }};margin-top:.1rem;" title="{{ $doc['aiNotes'] }}">🤖 {{ $doc['aiStatusLabel'] }}</p>
            @endif
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

@endif
</div>
