@extends('layouts.app-sidebar')
@section('title', 'Biblioteca de Medios')

@section('styles')
<style>
    .media-toolbar { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .media-toolbar .form-input, .media-toolbar .form-select { max-width: 220px; }
    .upload-zone { border: 2px dashed var(--border); border-radius: 12px; padding: 2.5rem; text-align: center; cursor: pointer; transition: all 0.2s; background: var(--card); margin-bottom: 1.5rem; }
    .upload-zone:hover, .upload-zone.dragover { border-color: var(--primary); background: rgba(59,130,196,0.04); }
    .upload-zone.dragover { transform: scale(1.01); }
    .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; }
    .media-item { border-radius: var(--radius); border: 2px solid var(--border); overflow: hidden; cursor: pointer; transition: all 0.15s; background: var(--card); }
    .media-item:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .media-item.selected { border-color: var(--primary); box-shadow: 0 0 0 2px var(--primary); }
    .media-thumb { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; }
    .media-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .media-thumb .file-icon { font-size: 2.5rem; color: var(--text-muted); }
    .media-info { padding: 0.6rem 0.75rem; }
    .media-info .fname { font-size: 0.78rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .media-info .fmeta { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem; }
    .detail-panel { position: fixed; right: 0; top: 0; bottom: 0; width: 360px; background: var(--card); border-left: 1px solid var(--border); z-index: 100; padding: 1.5rem; overflow-y: auto; transform: translateX(100%); transition: transform 0.2s; }
    .detail-panel.open { transform: translateX(0); }
    .detail-panel .close-btn { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted); }
    .detail-panel img { width: 100%; border-radius: var(--radius); margin-bottom: 1rem; }
    .upload-progress { margin-top: 0.5rem; }
    .upload-progress .bar { height: 4px; background: var(--border); border-radius: 2px; overflow: hidden; }
    .upload-progress .bar-fill { height: 100%; background: var(--primary); transition: width 0.3s; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Biblioteca de Medios</h2>
        <p class="text-muted">{{ $media->total() }} archivos</p>
    </div>
</div>

{{-- Upload zone --}}
<div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
    <div style="font-size: 2rem; color: var(--text-muted);">&#128228;</div>
    <p style="margin-top: 0.5rem; font-weight: 500;">Arrastra archivos aqui o haz click para subir</p>
    <p class="text-muted" style="font-size: 0.82rem; margin-top: 0.25rem;">Imagenes, documentos, PDF. Max 10MB por archivo.</p>
    <input type="file" id="fileInput" multiple style="display: none;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
    <div class="upload-progress" id="uploadProgress" style="display: none;">
        <div class="bar"><div class="bar-fill" id="uploadBar" style="width: 0%;"></div></div>
        <p id="uploadStatus" style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;"></p>
    </div>
</div>

{{-- Filters --}}
<div class="media-toolbar">
    <form method="GET" action="{{ route('admin.media.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <input type="text" name="search" class="form-input" placeholder="Buscar..." value="{{ request('search') }}">
        <select name="type" class="form-select" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            <option value="images" {{ request('type') === 'images' ? 'selected' : '' }}>Solo imagenes</option>
        </select>
        @if($folders->count())
        <select name="folder" class="form-select" onchange="this.form.submit()">
            <option value="">Todas las carpetas</option>
            @foreach($folders as $f)
            <option value="{{ $f }}" {{ request('folder') === $f ? 'selected' : '' }}>{{ $f }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filtrar</button>
        @if(request()->hasAny(['search', 'type', 'folder']))
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline" style="padding: 0.5rem 1rem;">Limpiar</a>
        @endif
    </form>
</div>

{{-- Grid --}}
<div class="media-grid" id="mediaGrid">
    @forelse($media as $item)
    <div class="media-item" data-id="{{ $item->id }}" onclick="showDetail({{ $item->id }})">
        <div class="media-thumb">
            @if($item->is_image)
                <img src="{{ $item->url }}" alt="{{ $item->alt_text ?? $item->filename }}" loading="lazy">
            @else
                <span class="file-icon">&#128196;</span>
            @endif
        </div>
        <div class="media-info">
            <div class="fname">{{ $item->filename }}</div>
            <div class="fmeta">{{ $item->human_size }} @if($item->width)&middot; {{ $item->width }}x{{ $item->height }}@endif</div>
        </div>
    </div>
    @empty
    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
        No hay archivos. Sube tu primer archivo arrastrando aqui arriba.
    </div>
    @endforelse
</div>

@if($media->hasPages())
<div style="margin-top: 1.5rem; display: flex; justify-content: center;">
    {{ $media->links() }}
</div>
@endif

{{-- Detail side panel --}}
<div class="detail-panel" id="detailPanel">
    <button class="close-btn" onclick="closeDetail()">&times;</button>
    <div id="detailContent"></div>
</div>
<div id="detailOverlay" style="display: none; position: fixed; inset: 0; z-index: 99;" onclick="closeDetail()"></div>

@php
$mediaJson = $media->map(fn($m) => [
    'id' => $m->id,
    'url' => $m->url,
    'filename' => $m->filename,
    'alt_text' => $m->alt_text,
    'title' => $m->title,
    'human_size' => $m->human_size,
    'width' => $m->width,
    'height' => $m->height,
    'mime_type' => $m->mime_type,
    'is_image' => $m->is_image,
    'created_at' => $m->created_at->format('d/m/Y H:i'),
])->keyBy('id');
@endphp
@endsection

@section('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var mediaData = @json($mediaJson);

// Drag and drop
var zone = document.getElementById('uploadZone');
['dragenter', 'dragover'].forEach(function(ev) {
    zone.addEventListener(ev, function(e) { e.preventDefault(); zone.classList.add('dragover'); });
});
['dragleave', 'drop'].forEach(function(ev) {
    zone.addEventListener(ev, function(e) { e.preventDefault(); zone.classList.remove('dragover'); });
});
zone.addEventListener('drop', function(e) { uploadFiles(e.dataTransfer.files); });
document.getElementById('fileInput').addEventListener('change', function() { uploadFiles(this.files); this.value = ''; });

function uploadFiles(files) {
    if (!files.length) return;
    var formData = new FormData();
    for (var i = 0; i < files.length; i++) formData.append('files[]', files[i]);

    var bar = document.getElementById('uploadBar');
    var status = document.getElementById('uploadStatus');
    document.getElementById('uploadProgress').style.display = 'block';
    bar.style.width = '30%';
    status.textContent = 'Subiendo ' + files.length + ' archivo(s)...';

    fetch('{{ route("admin.media.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        bar.style.width = '100%';
        status.textContent = 'Completado!';
        setTimeout(function() { location.reload(); }, 600);
    })
    .catch(function(err) {
        bar.style.width = '0%';
        status.textContent = 'Error al subir archivos.';
    });
}

// Detail panel
function showDetail(id) {
    var m = mediaData[id];
    if (!m) return;
    var html = '';
    if (m.is_image) {
        html += '<img src="' + m.url + '" alt="' + (m.alt_text || '') + '">';
    } else {
        html += '<div style="text-align:center;padding:2rem;font-size:3rem;color:var(--text-muted);">&#128196;</div>';
    }
    html += '<h4 style="font-size:0.92rem;margin-bottom:1rem;word-break:break-all;">' + m.filename + '</h4>';
    html += '<form onsubmit="saveDetail(event, ' + id + ')">';
    html += '<div class="form-group"><label class="form-label">Titulo</label><input type="text" name="title" class="form-input" value="' + (m.title || '') + '"></div>';
    html += '<div class="form-group"><label class="form-label">Texto alternativo</label><input type="text" name="alt_text" class="form-input" value="' + (m.alt_text || '') + '"></div>';
    html += '<div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:1rem;">';
    html += '<p>Tamaño: ' + m.human_size + '</p>';
    if (m.width) html += '<p>Dimensiones: ' + m.width + 'x' + m.height + '</p>';
    html += '<p>Tipo: ' + m.mime_type + '</p>';
    html += '<p>Subido: ' + m.created_at + '</p>';
    html += '</div>';
    html += '<div class="form-group"><label class="form-label">URL</label><input type="text" class="form-input" value="' + m.url + '" readonly onclick="this.select()"></div>';
    html += '<div style="display:flex;gap:0.5rem;">';
    html += '<button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>';
    html += '<button type="button" class="btn" style="color:var(--danger);" onclick="deleteMedia(' + id + ')">Eliminar</button>';
    html += '</div></form>';
    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailPanel').classList.add('open');
    document.getElementById('detailOverlay').style.display = 'block';
}

function closeDetail() {
    document.getElementById('detailPanel').classList.remove('open');
    document.getElementById('detailOverlay').style.display = 'none';
}

function saveDetail(e, id) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);
    fetch('{{ url("admin/media") }}/' + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mediaData[id].title = form.querySelector('[name=title]').value;
            mediaData[id].alt_text = form.querySelector('[name=alt_text]').value;
            closeDetail();
        }
    });
}

function deleteMedia(id) {
    if (!confirm('Eliminar este archivo?')) return;
    fetch('{{ url("admin/media") }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var el = document.querySelector('.media-item[data-id="' + id + '"]');
            if (el) el.remove();
            closeDetail();
        }
    });
}
</script>
@endsection
