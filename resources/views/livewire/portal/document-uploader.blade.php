<div>

{{-- ── Mensajes ──────────────────────────────────────────────────────────────── --}}
@if($successMsg)
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.65rem;"
     x-data x-init="setTimeout(() => $wire.clearMessages(), 4000)">
    <span style="color:#10b981;font-size:1rem;">✓</span>
    <span style="font-size:.83rem;font-weight:600;color:#166534;">{{ $successMsg }}</span>
</div>
@endif

@if($errorMsg)
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.65rem;">
    <span style="color:#ef4444;font-size:1rem;">⚠</span>
    <span style="font-size:.83rem;font-weight:600;color:#991b1b;">{{ $errorMsg }}</span>
</div>
@endif

{{-- ── Botón abrir formulario ─────────────────────────────────────────────────── --}}
@if(!$showForm)
<div style="margin-bottom:1.25rem;">
    <button wire:click="$set('showForm', true)"
            style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:#1D4ED8;color:#fff;font-size:.82rem;font-weight:600;border:none;border-radius:8px;cursor:pointer;transition:opacity .15s;"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Subir documento
    </button>
</div>
@endif

{{-- ── Formulario de upload ──────────────────────────────────────────────────── --}}
@if($showForm)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;"
     x-data="docUploader()" x-init="init()">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:.95rem;color:#0f172a;">Subir nuevo documento</span>
        <button wire:click="$set('showForm', false)" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:#94a3b8;line-height:1;">&times;</button>
    </div>

    <form wire:submit="upload">

        {{-- Categoría --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.4rem;">Categoría *</label>
            <select wire:model="category"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;background:#fff;">
                <option value="">Selecciona una categoría...</option>
                @foreach($availableCategories as $val => $lbl)
                <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
            </select>
            @error('category') <p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p> @enderror
        </div>

        {{-- Nombre --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.4rem;">Nombre del documento *</label>
            <input wire:model="label" type="text" placeholder="Ej: INE Frente, Contrato firmado..."
                   style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;">
            @error('label') <p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p> @enderror
        </div>

        {{-- Zona de archivo --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:.4rem;">Archivo *</label>

            {{-- Drop zone --}}
            <div @dragover.prevent="dragging=true"
                 @dragleave="dragging=false"
                 @drop.prevent="handleDrop($event)"
                 :style="dragging ? 'border-color:#1D4ED8;background:#eff6ff;' : ''"
                 style="border:2px dashed #e2e8f0;border-radius:10px;padding:1.75rem;text-align:center;cursor:pointer;transition:all .2s;"
                 onclick="document.getElementById('doc-file-input-{{ $this->getId() }}').click()">

                <div wire:loading wire:target="file" style="color:#1D4ED8;">
                    <svg style="width:24px;height:24px;display:inline-block;animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83"/></svg>
                    <p style="font-size:.82rem;color:#1D4ED8;margin-top:.5rem;">Subiendo...</p>
                </div>

                <div wire:loading.remove wire:target="file">
                    @if($file)
                    <div style="color:#10b981;">
                        <svg style="width:24px;height:24px;display:inline-block;margin-bottom:.4rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <p style="font-size:.82rem;font-weight:600;color:#0f172a;">{{ $file->getClientOriginalName() }}</p>
                        <p style="font-size:.72rem;color:#64748b;">{{ round($file->getSize() / 1024) }} KB</p>
                    </div>
                    @else
                    <div style="opacity:.5;">
                        <svg style="width:28px;height:28px;display:inline-block;margin-bottom:.5rem;color:#94a3b8;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p style="font-size:.82rem;color:#64748b;"><strong style="color:#1D4ED8;">Haz clic para seleccionar</strong> o arrastra aquí</p>
                        <p style="font-size:.72rem;color:#94a3b8;margin-top:.25rem;">PDF, JPG, PNG, DOC — máx. 10 MB</p>
                    </div>
                    @endif
                </div>
            </div>

            <input id="doc-file-input-{{ $this->getId() }}"
                   type="file"
                   wire:model="file"
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                   style="display:none;">
            @error('file') <p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p> @enderror
        </div>

        {{-- Acciones --}}
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <button type="button" wire:click="$set('showForm', false)"
                    style="padding:.5rem 1rem;font-size:.82rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">
                Cancelar
            </button>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="upload,file"
                    style="padding:.5rem 1.25rem;font-size:.82rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:opacity .15s;"
                    wire:loading.class="opacity-60">
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
        $statusColor = match($doc['status']) {
            'verified' => '#10b981',
            'rejected' => '#ef4444',
            'received' => '#3b82f6',
            default    => '#f59e0b',
        };
    @endphp
    <div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;background:#fff;border:1px solid #e2e8f0;border-radius:9px;transition:border-color .15s;"
         onmouseover="this.style.borderColor='#cbd5e1'" onmouseout="this.style.borderColor='#e2e8f0'">

        {{-- Ícono tipo --}}
        <div style="width:32px;height:32px;border-radius:7px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">📄</div>

        {{-- Info --}}
        <div style="flex:1;min-width:0;">
            <p style="font-weight:600;font-size:.83rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc['label'] }}</p>
            <p style="font-size:.7rem;color:#64748b;margin-top:.1rem;">
                {{ $doc['category'] }}
                @if($doc['size']) &middot; {{ $doc['size'] }} @endif
                &middot; {{ $doc['date'] }}
            </p>
        </div>

        {{-- Badge --}}
        <span style="flex-shrink:0;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ $statusColor }}20;color:{{ $statusColor }};">
            {{ $doc['statusLabel'] }}
        </span>

        {{-- Acciones --}}
        <div style="flex-shrink:0;display:flex;gap:.3rem;">
            <a href="{{ route('portal.documents.download', $doc['id']) }}"
               style="display:inline-flex;align-items:center;gap:.25rem;padding:.3rem .6rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.72rem;font-weight:600;color:#64748b;text-decoration:none;transition:all .15s;"
               onmouseover="this.style.borderColor='#1D4ED8';this.style.color='#1D4ED8'" onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#64748b'">
                ↓ Descargar
            </a>
            @if($doc['canDelete'])
            <button wire:click="deleteDocument({{ $doc['id'] }})"
                    wire:confirm="¿Eliminar este documento?"
                    style="display:inline-flex;align-items:center;padding:.3rem .5rem;border:1px solid #fecaca;border-radius:6px;font-size:.72rem;color:#ef4444;background:none;cursor:pointer;transition:all .15s;"
                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
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

@script
<script>
    function docUploader() {
        return {
            dragging: false,
            init() {},
            handleDrop(e) {
                this.dragging = false;
                const inputId = 'doc-file-input-{{ $this->getId() }}';
                const input = document.getElementById(inputId);
                if (input && e.dataTransfer.files.length) {
                    // Livewire file input requiere DataTransfer nativo
                    const dt = new DataTransfer();
                    dt.items.add(e.dataTransfer.files[0]);
                    input.files = dt.files;
                    input.dispatchEvent(new Event('change'));
                }
            }
        }
    }
</script>
@endscript

@style
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endstyle
