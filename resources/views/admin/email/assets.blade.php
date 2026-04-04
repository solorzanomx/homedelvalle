@extends('layouts.app-sidebar')
@section('title', 'Assets de Email')

@section('styles')
<style>
    .asset-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:1rem; }
    .asset-card {
        background:var(--card); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden;
        transition:all 0.15s; position:relative;
    }
    .asset-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.08); }
    .asset-thumb {
        width:100%; height:130px; object-fit:cover; display:block; background:var(--bg);
        border-bottom:1px solid var(--border);
    }
    .asset-info { padding:0.65rem 0.75rem; }
    .asset-name { font-size:0.8rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:0.2rem; }
    .asset-meta { font-size:0.7rem; color:var(--text-muted); display:flex; justify-content:space-between; }
    .asset-actions { display:flex; gap:0.35rem; padding:0 0.75rem 0.65rem; }
    .upload-zone {
        border:2px dashed var(--border); border-radius:var(--radius); padding:2rem; text-align:center;
        cursor:pointer; transition:all 0.15s; background:var(--card);
    }
    .upload-zone:hover, .upload-zone.dragover { border-color:var(--primary); background:rgba(59,130,196,0.04); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Assets de Email</h2>
        <p class="text-muted">Galeria de imagenes para usar en templates de correo</p>
    </div>
    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline">&#9998; Templates</a>
</div>

{{-- Upload Zone --}}
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.email.assets.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                <input type="file" id="fileInput" name="image" accept="image/*" style="display:none" onchange="handleFileSelect(this)">
                <div style="font-size:2rem; margin-bottom:0.5rem;">&#128247;</div>
                <p style="font-weight:500; margin-bottom:0.25rem;">Arrastra una imagen o haz clic aqui</p>
                <p class="text-muted" style="font-size:0.78rem;">JPG, PNG, GIF, WebP, SVG (max 5MB)</p>
            </div>
            <div id="uploadPreview" style="display:none; margin-top:1rem;">
                <div style="display:flex; align-items:center; gap:1rem;">
                    <img id="previewImg" style="width:80px; height:80px; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                    <div style="flex:1;">
                        <div class="form-group" style="margin-bottom:0.5rem;">
                            <label class="form-label">Nombre del asset</label>
                            <input type="text" name="name" id="assetName" class="form-input" placeholder="Logo, Banner, Footer...">
                        </div>
                        <div style="display:flex; gap:0.5rem;">
                            <button type="submit" class="btn btn-primary">Subir Imagen</button>
                            <button type="button" class="btn btn-outline" onclick="cancelUpload()">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Gallery --}}
<div class="card">
    <div class="card-header">
        <h3>Galeria ({{ $assets->count() }} imagenes)</h3>
    </div>
    <div class="card-body">
        @if($assets->count())
            <div class="asset-grid">
                @foreach($assets as $asset)
                <div class="asset-card">
                    <img src="{{ $asset->url }}" class="asset-thumb" alt="{{ $asset->name }}">
                    <div class="asset-info">
                        <div class="asset-name" title="{{ $asset->name }}">{{ $asset->name }}</div>
                        <div class="asset-meta">
                            <span>{{ $asset->human_size }}</span>
                            <span>{{ $asset->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="asset-actions">
                        <button type="button" class="btn btn-sm btn-outline" style="flex:1; justify-content:center; font-size:0.72rem;" onclick="copyUrl('{{ $asset->url }}')">Copiar URL</button>
                        <form method="POST" action="{{ route('admin.email.assets.destroy', $asset) }}" style="display:inline" onsubmit="return confirm('Eliminar esta imagen?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" style="font-size:0.72rem;">Eliminar</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted" style="padding:2rem;">
                <p style="font-size:1.5rem; margin-bottom:0.5rem;">&#128444;</p>
                <p>No hay imagenes subidas aun.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
var dropZone = document.getElementById('dropZone');
['dragenter', 'dragover'].forEach(function(e) {
    dropZone.addEventListener(e, function(ev) { ev.preventDefault(); dropZone.classList.add('dragover'); });
});
['dragleave', 'drop'].forEach(function(e) {
    dropZone.addEventListener(e, function(ev) { ev.preventDefault(); dropZone.classList.remove('dragover'); });
});
dropZone.addEventListener('drop', function(ev) {
    var file = ev.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        document.getElementById('fileInput').files = ev.dataTransfer.files;
        handleFileSelect(document.getElementById('fileInput'));
    }
});

function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { alert('Maximo 5MB'); input.value = ''; return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('assetName').value = file.name.replace(/\.[^/.]+$/, '');
        document.getElementById('uploadPreview').style.display = 'block';
        dropZone.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function cancelUpload() {
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadPreview').style.display = 'none';
    document.getElementById('dropZone').style.display = 'block';
}

function copyUrl(url) {
    var fullUrl = window.location.origin + url;
    navigator.clipboard.writeText(fullUrl).then(function() { alert('URL copiada: ' + fullUrl); });
}
</script>
@endsection
