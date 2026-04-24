@extends('layouts.portal')
@section('title', 'Mis Documentos')

@section('styles')
.doc-stats { display:flex; gap:.65rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.doc-stat  { display:flex; align-items:center; gap:.5rem; padding:.5rem .85rem; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); white-space:nowrap; }
.doc-stat-icon { width:30px; height:30px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:.8rem; flex-shrink:0; }
.doc-stat-val  { font-size:1rem; font-weight:700; color:var(--text); line-height:1; }
.doc-stat-lbl  { font-size:.68rem; color:var(--text-muted); }

.section-title { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); margin:0 0 .65rem; }

.doc-list { display:flex; flex-direction:column; gap:.5rem; }
.doc-row  {
    display:flex; align-items:center; gap:.75rem;
    padding:.7rem 1rem; background:var(--card);
    border:1px solid var(--border); border-radius:var(--radius);
    transition:border-color .15s;
}
.doc-row:hover { border-color:var(--primary); }
.doc-icon  { width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; background:var(--bg); border:1px solid var(--border); }
.doc-info  { flex:1; min-width:0; }
.doc-name  { font-weight:600; font-size:.88rem; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.doc-meta  { font-size:.72rem; color:var(--text-muted); margin-top:.1rem; }
.doc-status { flex-shrink:0; }
.doc-reason { font-size:.72rem; color:#ef4444; margin-top:.15rem; }
.doc-actions { display:flex; gap:.3rem; flex-shrink:0; }
.doc-dl { display:inline-flex; align-items:center; gap:.25rem; padding:.3rem .65rem; border:1px solid var(--border); border-radius:6px; font-size:.75rem; font-weight:600; color:var(--text-muted); text-decoration:none; background:var(--bg); transition:all .15s; }
.doc-dl:hover { border-color:var(--primary); color:var(--primary); }

/* Upload modal */
.upload-zone { border:2px dashed var(--border); border-radius:var(--radius); padding:2rem; text-align:center; cursor:pointer; transition:border-color .2s; }
.upload-zone:hover, .upload-zone.dragover { border-color:var(--primary); background:rgba(var(--primary-rgb),.03); }
.upload-zone-icon { font-size:2rem; margin-bottom:.5rem; opacity:.5; }
.upload-zone-text { font-size:.85rem; color:var(--text-muted); }
.upload-zone-text strong { color:var(--primary); }

@media (max-width:600px) {
    .doc-row { flex-wrap:wrap; }
    .doc-actions { width:100%; justify-content:flex-end; }
}
@endsection

@section('content')
@php
    $totalDocs   = $documents->count() + $captacionDocuments->count();
    $approved    = $documents->where('status','verified')->count()
                 + $captacionDocuments->where('captacion_status','aprobado')->count();
    $inReview    = $documents->whereIn('status',['pending','received'])->count()
                 + $captacionDocuments->where('captacion_status','pendiente')->count();
    $rejected    = $documents->where('status','rejected')->count()
                 + $captacionDocuments->where('captacion_status','rechazado')->count();
    $catLabels   = $allCategories ?? [];
@endphp

<div class="page-header">
    <div>
        <h2>Mis Documentos</h2>
        <p style="color:var(--text-muted);font-size:.82rem;">{{ $totalDocs }} documento{{ $totalDocs !== 1 ? 's' : '' }} en total</p>
    </div>
    @if($client)
    <button class="btn btn-primary" onclick="document.getElementById('upload-modal').style.display='flex'">
        &#43; Subir documento
    </button>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1rem;">{{ session('error') }}</div>
@endif

@if(!$client || $totalDocs === 0)
{{-- Empty state --}}
<div class="card">
    <div class="card-body" style="text-align:center;padding:3rem 1.5rem;">
        <div style="font-size:2.5rem;opacity:.3;margin-bottom:.75rem;">&#128196;</div>
        <div style="font-weight:600;margin-bottom:.35rem;">Sin documentos aún</div>
        <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.25rem;">Aquí aparecerán todos los documentos relacionados con tu proceso.</div>
        @if($client)
        <button class="btn btn-primary" onclick="document.getElementById('upload-modal').style.display='flex'">Subir mi primer documento</button>
        @endif
    </div>
</div>
@else

{{-- Stats --}}
<div class="doc-stats">
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(16,185,129,.1);color:#10b981;">&#128196;</div>
        <div><div class="doc-stat-val">{{ $totalDocs }}</div><div class="doc-stat-lbl">Total</div></div>
    </div>
    @if($approved > 0)
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(16,185,129,.1);color:#10b981;">&#10003;</div>
        <div><div class="doc-stat-val" style="color:#10b981;">{{ $approved }}</div><div class="doc-stat-lbl">Aprobados</div></div>
    </div>
    @endif
    @if($inReview > 0)
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;">&#9679;</div>
        <div><div class="doc-stat-val" style="color:#f59e0b;">{{ $inReview }}</div><div class="doc-stat-lbl">En revisión</div></div>
    </div>
    @endif
    @if($rejected > 0)
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(239,68,68,.1);color:#ef4444;">&#10007;</div>
        <div><div class="doc-stat-val" style="color:#ef4444;">{{ $rejected }}</div><div class="doc-stat-lbl">Rechazados</div></div>
    </div>
    @endif
</div>

{{-- Captacion documents --}}
@if($captacionDocuments->isNotEmpty())
<div style="margin-bottom:1.5rem;">
    <p class="section-title">Evaluación de Propiedad</p>
    <div class="doc-list">
        @foreach($captacionDocuments as $doc)
        @php
            $statusColor = match($doc->captacion_status) {
                'aprobado'  => '#10b981',
                'rechazado' => '#ef4444',
                default     => '#f59e0b',
            };
            $statusLabel = match($doc->captacion_status) {
                'aprobado'  => 'Aprobado',
                'rechazado' => 'Rechazado',
                default     => 'En revisión',
            };
            $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
            $icon = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? '&#128247;' : ($ext === 'pdf' ? '&#128196;' : '&#128462;');
        @endphp
        <div class="doc-row">
            <div class="doc-icon">{{ $icon }}</div>
            <div class="doc-info">
                <div class="doc-name">{{ $doc->label ?? $doc->file_name }}</div>
                <div class="doc-meta">
                    {{ $catLabels[$doc->category] ?? $doc->category }}
                    &middot; {{ $doc->created_at->format('d/m/Y') }}
                    @if($doc->file_size) &middot; {{ round($doc->file_size / 1024) }} KB @endif
                </div>
                @if($doc->captacion_status === 'rechazado' && $doc->rejection_reason)
                <div class="doc-reason">&#9888; {{ $doc->rejection_reason }}</div>
                @endif
            </div>
            <div class="doc-status">
                <span class="badge" style="background:{{ $statusColor }}20;color:{{ $statusColor }};">{{ $statusLabel }}</span>
            </div>
            <div class="doc-actions">
                <a href="{{ route('portal.documents.download', $doc->id) }}" class="doc-dl" title="Descargar">
                    &#8615; Descargar
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Other documents (rental, general) --}}
@if($documents->isNotEmpty())
<div style="margin-bottom:1.5rem;">
    <p class="section-title">Otros documentos</p>
    <div class="doc-list">
        @foreach($documents as $doc)
        @php
            $statusColor = match($doc->status) {
                'verified' => '#10b981',
                'rejected' => '#ef4444',
                'received' => '#3b82f6',
                default    => '#f59e0b',
            };
            $statusLabel = match($doc->status) {
                'verified' => 'Verificado',
                'rejected' => 'Rechazado',
                'received' => 'Recibido',
                default    => 'Pendiente',
            };
            $ext  = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
            $icon = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? '&#128247;' : ($ext === 'pdf' ? '&#128196;' : '&#128462;');
        @endphp
        <div class="doc-row">
            <div class="doc-icon">{{ $icon }}</div>
            <div class="doc-info">
                <div class="doc-name">{{ $doc->label ?? $doc->file_name }}</div>
                <div class="doc-meta">
                    {{ $catLabels[$doc->category] ?? $doc->category }}
                    @if($doc->rentalProcess)
                     &middot; Renta #{{ $doc->rentalProcess->id }}
                    @endif
                    &middot; {{ $doc->created_at->format('d/m/Y') }}
                    @if($doc->file_size) &middot; {{ round($doc->file_size / 1024) }} KB @endif
                </div>
                @if($doc->status === 'rejected' && $doc->rejection_reason)
                <div class="doc-reason">&#9888; {{ $doc->rejection_reason }}</div>
                @endif
            </div>
            <div class="doc-status">
                <span class="badge" style="background:{{ $statusColor }}20;color:{{ $statusColor }};">{{ $statusLabel }}</span>
            </div>
            <div class="doc-actions">
                <a href="{{ route('portal.documents.download', $doc->id) }}" class="doc-dl" title="Descargar">
                    &#8615; Descargar
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif {{-- end totalDocs > 0 --}}

{{-- Upload Modal --}}
@if($client)
<div id="upload-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:var(--card);border-radius:12px;width:100%;max-width:480px;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;font-size:1rem;">Subir documento</span>
            <button onclick="document.getElementById('upload-modal').style.display='none'" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--text-muted);">&times;</button>
        </div>
        <form method="POST" action="{{ route('portal.documents.upload') }}" enctype="multipart/form-data" style="padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
            @csrf
            <div>
                <label class="form-label">Categoría</label>
                <select name="category" class="form-control" required>
                    <option value="">Selecciona una categoría...</option>
                    @foreach($allCategories as $val => $lbl)
                    <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Archivo</label>
                <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                    <div class="upload-zone-icon">&#128196;</div>
                    <div class="upload-zone-text" id="drop-text">
                        <strong>Haz clic para seleccionar</strong> o arrastra el archivo aquí<br>
                        <span style="font-size:.75rem;">PDF, JPG, PNG, DOC — máx. 10 MB</span>
                    </div>
                </div>
                <input type="file" id="file-input" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display:none;" required onchange="updateFileName(this)">
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('upload-modal').style.display='none'" class="btn btn-outline">Cancelar</button>
                <button type="submit" class="btn btn-primary">Subir</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function updateFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('drop-text').innerHTML =
            '<strong>' + input.files[0].name + '</strong><br><span style="font-size:.75rem;">' +
            Math.round(input.files[0].size / 1024) + ' KB</span>';
    }
}
(function() {
    var zone = document.getElementById('drop-zone');
    if (!zone) return;
    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault(); zone.classList.remove('dragover');
        var fi = document.getElementById('file-input');
        if (e.dataTransfer.files.length) { fi.files = e.dataTransfer.files; updateFileName(fi); }
    });
})();
</script>
@endsection
