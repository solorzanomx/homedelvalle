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

{{-- Botón abrir --}}
@if(!$showForm)
<div style="margin-bottom:1.25rem;">
    <button wire:click="$set('showForm', true)"
            style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:#1D4ED8;color:#fff;font-size:.82rem;font-weight:600;border:none;border-radius:8px;cursor:pointer;">
        ↑ Subir documento
    </button>
</div>
@endif

{{-- Formulario --}}
@if($showForm)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:.95rem;color:#0f172a;">Subir nuevo documento</span>
        <button wire:click="$set('showForm', false)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;">&times;</button>
    </div>

    {{-- Categoría --}}
    <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">Categoría *</label>
        <select wire:model="category" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;background:#fff;">
            <option value="">Selecciona una categoría...</option>
            @foreach($availableCategories as $val => $lbl)
            <option value="{{ $val }}">{{ $lbl }}</option>
            @endforeach
        </select>
        @error('category')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
    </div>

    {{-- Nombre --}}
    <div style="margin-bottom:1rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">Nombre del documento *</label>
        <input wire:model="label" type="text" placeholder="Ej: INE Frente, Contrato firmado..."
               style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:.5rem .75rem;font-size:.85rem;color:#0f172a;">
        @error('label')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
    </div>

    {{-- Archivo --}}
    <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.4rem;">Archivo *</label>
        <div onclick="document.getElementById('fi-{{ $this->getId() }}').click()"
             style="border:2px dashed #e2e8f0;border-radius:10px;padding:1.75rem;text-align:center;cursor:pointer;transition:border-color .2s;"
             id="dz-{{ $this->getId() }}">
            <div wire:loading wire:target="file" style="padding:.5rem 0;">
                <div style="width:22px;height:22px;border:3px solid #bfdbfe;border-top-color:#1D4ED8;border-radius:50%;display:inline-block;animation:lw-spin .7s linear infinite;margin-bottom:.4rem;"></div>
                <p style="font-size:.82rem;color:#1D4ED8;">Procesando...</p>
            </div>
            <div wire:loading.remove wire:target="file">
                @if($file)
                <p style="font-size:.85rem;font-weight:600;color:#0f172a;">📄 {{ $file->getClientOriginalName() }}</p>
                <p style="font-size:.72rem;color:#64748b;">{{ round($file->getSize()/1024) }} KB</p>
                @else
                <p style="font-size:.83rem;color:#64748b;"><strong style="color:#1D4ED8;">Haz clic</strong> o arrastra aquí<br><span style="font-size:.72rem;color:#94a3b8;">PDF, JPG, PNG, DOC — máx. 10 MB</span></p>
                @endif
            </div>
        </div>
        <input id="fi-{{ $this->getId() }}" type="file" wire:model="file"
               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;">
        @error('file')<p style="font-size:.72rem;color:#ef4444;margin-top:.3rem;">{{ $message }}</p>@enderror
    </div>

    {{-- Botones --}}
    <div style="display:flex;gap:.75rem;justify-content:flex-end;">
        <button type="button" wire:click="$set('showForm', false)"
                style="padding:.5rem 1rem;font-size:.82rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;">
            Cancelar
        </button>
        <button type="button" wire:click="upload"
                wire:loading.attr="disabled" wire:target="upload"
                style="padding:.5rem 1.25rem;font-size:.82rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:8px;cursor:pointer;min-width:130px;">
            <span wire:loading.remove wire:target="upload">Subir documento</span>
            <span wire:loading wire:target="upload" style="display:inline-flex;align-items:center;gap:.4rem;">
                <span style="width:12px;height:12px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;display:inline-block;animation:lw-spin .7s linear infinite;"></span>
                Subiendo...
            </span>
        </button>
    </div>
</div>
@endif

{{-- Lista --}}
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

{{-- Drag & drop JS — dentro del root div --}}
<script>
(function(){
    function initDz(){
        var dz = document.getElementById('dz-{{ $this->getId() }}');
        var fi = document.getElementById('fi-{{ $this->getId() }}');
        if(!dz || dz._ready) return;
        dz._ready = true;
        dz.addEventListener('dragover',function(e){e.preventDefault();dz.style.borderColor='#1D4ED8';dz.style.background='#eff6ff';});
        dz.addEventListener('dragleave',function(){dz.style.borderColor='#e2e8f0';dz.style.background='';});
        dz.addEventListener('drop',function(e){
            e.preventDefault();dz.style.borderColor='#e2e8f0';dz.style.background='';
            if(fi&&e.dataTransfer.files.length){var dt=new DataTransfer();dt.items.add(e.dataTransfer.files[0]);fi.files=dt.files;fi.dispatchEvent(new Event('change'));}
        });
    }
    document.addEventListener('DOMContentLoaded',initDz);
    document.addEventListener('livewire:initialized',initDz);
    document.addEventListener('livewire:updated',initDz);
})();
</script>

</div>
