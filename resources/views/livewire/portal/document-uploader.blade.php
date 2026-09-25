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

{{-- ═══════════ MODO CASILLA ÚNICA (una sola categoría, hasta $maxSlots) ═══════════ --}}
@if($this->isSingleSlot())
    {{-- Ya subidos (puede haber varios si $maxSlots > 1, ej. últimos 3 comprobantes) --}}
    @foreach($documents as $existing)
    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;background:#fff;border:1px solid #e2e8f0;border-radius:9px;margin-bottom:.5rem;">
        @if($existing['thumbUrl'])
            <img src="{{ $existing['thumbUrl'] }}" style="width:44px;height:44px;object-fit:cover;border-radius:7px;border:1px solid #e2e8f0;flex-shrink:0;">
        @else
            <div style="width:44px;height:44px;border-radius:7px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">📄</div>
        @endif
        <div style="flex:1;min-width:0;">
            <p style="font-weight:600;font-size:.83rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $existing['label'] }}</p>
            <p style="font-size:.7rem;color:#64748b;">{{ $existing['size'] }} &middot; {{ $existing['date'] }}</p>
            @if(!empty($existing['rejectionReason']))
            <p style="font-size:.72rem;color:#b91c1c;margin-top:.2rem;font-weight:600;">⚠ Rechazado: {{ $existing['rejectionReason'] }}<br><span style="font-weight:400;">Elimínalo y súbelo de nuevo corregido.</span></p>
            @elseif($existing['status'] === 'rejected')
            <p style="font-size:.72rem;color:#b91c1c;margin-top:.2rem;font-weight:600;">⚠ Documento rechazado. Elimínalo y súbelo de nuevo.</p>
            @endif
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
            <button wire:click="deleteDocument({{ $existing['id'] }})" onclick="return confirm('¿Eliminar este documento?')" style="padding:.3rem .5rem;border:1px solid #fecaca;border-radius:6px;font-size:.72rem;color:#ef4444;background:none;cursor:pointer;">✕</button>
            @endif
        </div>
    </div>
    @endforeach

    @if($remainingSlots > 0)
        {{-- Casilla para subir la siguiente --}}
        @if($maxSlots > 1)
        <p style="font-size:.72rem;color:#64748b;margin-bottom:.35rem;">
            {{ $remainingSlots < $maxSlots ? 'Sube el comprobante ' . ($maxSlots - $remainingSlots + 1) . ' de ' . $maxSlots : 'Sube los últimos ' . $maxSlots . ' comprobantes' }}
        </p>
        @endif
        @include('portal._upload_tips', ['category' => $singleCategory])
        <div style="display:flex;gap:.5rem;align-items:stretch;">
            <label style="display:block;flex:1;">
                <div id="slot-dz-{{ $this->getId() }}"
                     style="border:2px dashed #e2e8f0;border-radius:10px;padding:.9rem 1rem;text-align:center;cursor:pointer;transition:border-color .2s;height:100%;box-sizing:border-box;">
                    <span wire:loading.remove wire:target="file" style="font-size:.8rem;color:#64748b;">
                        <strong style="color:#1D4ED8;">Subir archivo</strong> — PDF, JPG o PNG
                    </span>
                    <span wire:loading wire:target="upload" style="font-size:.8rem;color:#1D4ED8;display:inline-flex;align-items:center;gap:.4rem;">
                        <span class="lw-spinner"></span> Revisando que se lea bien...
                    </span>
                    <span wire:loading wire:target="file" style="font-size:.8rem;color:#1D4ED8;display:inline-flex;align-items:center;gap:.4rem;">
                        <span class="lw-spinner"></span> Subiendo...
                    </span>
                </div>
                {{-- SIN capture="environment" (2026-09-25): forzaba la cámara al tocar
                     "Subir archivo" y el cliente no podía elegir un PDF ni una
                     captura — terminaba fotografiando la pantalla de su teléfono.
                     Sin el atributo el celular ofrece cámara, fotos o archivos. --}}
                <input id="slot-input-{{ $singleCategory }}" type="file" wire:model="file" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
            </label>
            @if($isIdCategory)
            <button type="button" onclick="idCamOpen('{{ $singleCategory }}')"
                    style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;padding:0 1rem;border:1px solid #1D4ED8;border-radius:10px;background:#eff6ff;color:#1D4ED8;cursor:pointer;font-size:.72rem;font-weight:600;">
                <span style="font-size:1.1rem;">📷</span>
                Usar cámara
            </button>
            @endif
        </div>
        @if($isIdCategory)
        <p style="font-size:.7rem;color:#94a3b8;margin-top:.35rem;">
            💡 Mejor usa "Usar cámara" — encuadra la identificación sola, sin espacio alrededor, para que se lea bien.
        </p>
        @endif
    @endif

        @if($isIdCategory)
        {{-- Modal de cámara guiada — se abre/cierra por JS, vive oculto en el DOM.
             Los ids usan la categoría (única por instancia) en vez del id de
             Livewire, para poder abrir la siguiente cámara (frente→reverso)
             desde otro componente sin necesitar un mapeo aparte. --}}
        <div id="cam-modal-{{ $singleCategory }}" hidden style="position:fixed;inset:0;background:#000;z-index:99999;display:flex;flex-direction:column;">
            <video id="cam-video-{{ $singleCategory }}" autoplay playsinline muted style="flex:1;width:100%;height:100%;object-fit:cover;"></video>
            <canvas id="cam-canvas-{{ $singleCategory }}" style="display:none;"></canvas>
            <img id="cam-preview-{{ $singleCategory }}" style="display:none;flex:1;width:100%;height:100%;object-fit:contain;background:#000;">

            {{-- Recuadro guía, proporción de una credencial (85.6x54mm) —
                 id-{{ $singleCategory }} porque idCamCapture() recorta la
                 foto a exactamente esta caja, no manda la pantalla completa. --}}
            <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
                <div id="cam-guidebox-{{ $singleCategory }}" style="width:min(88vw, 560px);aspect-ratio:{{ $cameraAspectRatio }};border:3px solid #fff;border-radius:14px;box-shadow:0 0 0 2000px rgba(0,0,0,.45);"></div>
            </div>

            <div style="position:absolute;top:0;left:0;right:0;padding:1rem 1.25rem;padding-top:calc(1rem + env(safe-area-inset-top, 0px));background:linear-gradient(rgba(0,0,0,.55),transparent);display:flex;align-items:center;justify-content:space-between;">
                <span style="color:#fff;font-size:.9rem;font-weight:700;">{{ $cameraSideLabel }}</span>
                <button type="button" onclick="idCamClose('{{ $singleCategory }}')" style="background:rgba(255,255,255,.15);border:none;color:#fff;width:34px;height:34px;border-radius:50%;font-size:1.1rem;cursor:pointer;">✕</button>
            </div>

            <div id="cam-controls-live-{{ $singleCategory }}" style="position:absolute;bottom:0;left:0;right:0;padding:1.5rem;padding-bottom:calc(1.5rem + env(safe-area-inset-bottom, 0px));display:flex;justify-content:center;">
                <button type="button" onclick="idCamCapture('{{ $singleCategory }}')" style="width:68px;height:68px;border-radius:50%;background:#fff;border:4px solid rgba(255,255,255,.4);cursor:pointer;"></button>
            </div>

            <div id="cam-controls-preview-{{ $singleCategory }}" style="display:none;position:absolute;bottom:0;left:0;right:0;padding:1.5rem;padding-bottom:calc(1.5rem + env(safe-area-inset-bottom, 0px));gap:.75rem;justify-content:center;">
                <button type="button" onclick="idCamRetake('{{ $singleCategory }}')" style="padding:.75rem 1.5rem;border-radius:10px;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);font-size:.85rem;font-weight:600;cursor:pointer;">↺ Repetir</button>
                <button type="button" onclick="idCamConfirm('{{ $singleCategory }}')" style="padding:.75rem 1.5rem;border-radius:10px;background:#1D4ED8;color:#fff;border:none;font-size:.85rem;font-weight:700;cursor:pointer;">✓ Usar esta foto</button>
            </div>

            <div id="cam-error-{{ $singleCategory }}" hidden style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:2rem;text-align:center;color:#fff;font-size:.85rem;"></div>
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

    @if($category)
    <div style="margin-bottom:1rem;">@include('portal._upload_tips', ['category' => $category, 'open' => true])</div>
    @endif

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
        <button type="button" wire:click="upload" wire:loading.attr="disabled" wire:target="upload"
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
            @if(!empty($doc['rejectionReason']))
            <p style="font-size:.72rem;color:#b91c1c;margin-top:.2rem;font-weight:600;">⚠ Rechazado: {{ $doc['rejectionReason'] }}<br><span style="font-weight:400;">Elimínalo y súbelo de nuevo corregido.</span></p>
            @elseif($doc['status'] === 'rejected')
            <p style="font-size:.72rem;color:#b91c1c;margin-top:.2rem;font-weight:600;">⚠ Documento rechazado. Elimínalo y súbelo de nuevo.</p>
            @endif
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
