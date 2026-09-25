{{-- Una categoría de documento con sus archivos. Espera: $catKey, $catLabel, $catDocs, $rental --}}
<div style="margin-bottom:.75rem;">
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem;">
        @if($catDocs->where('status', 'verified')->count() > 0)
            <span style="color:var(--success);font-size:1rem;">&#10003;</span>
        @else
            <span style="color:#f59e0b;font-size:1rem;">&#9679;</span>
        @endif
        <span style="font-size:.82rem;font-weight:600;">{{ $catLabel }}</span>
        <span style="font-size:.72rem;color:var(--text-muted);">({{ $catDocs->count() }})</span>
    </div>

    @foreach($catDocs as $doc)
    @php
        $isImage = in_array($doc->mime_type, ['image/jpeg', 'image/jpg', 'image/png']);
        $isPdf = $doc->mime_type === 'application/pdf';
        $canView = $isImage || $isPdf;
        $badge = match($doc->status) { 'verified' => 'green', 'rejected' => 'red', 'received' => 'blue', default => 'yellow' };
    @endphp
    <div class="doc-item" id="docrow-{{ $doc->id }}"
         data-doc-id="{{ $doc->id }}"
         data-status="{{ $doc->status }}"
         data-title="{{ $catLabel }}"
         data-file="{{ $doc->file_name }}"
         data-kind="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : 'other') }}"
         data-preview="{{ route('documents.preview', $doc->id) }}"
         data-download="{{ route('documents.download', $doc->id) }}"
         data-reason="{{ $doc->rejection_reason }}"
         data-ai="{{ $doc->ai_verification_status ? $doc->ai_verification_status_label . ($doc->ai_verification_notes ? ' — ' . $doc->ai_verification_notes : '') : '' }}"
         data-ai-status="{{ $doc->ai_verification_status }}"
         data-quality="{{ $doc->quality_status === 'warn' ? $doc->quality_notes : '' }}"
         data-meta="{{ $doc->uploader->name ?? '' }} · {{ $doc->created_at->format('d/m/Y H:i') }}">
        @if($isImage)
            <img src="{{ route('documents.preview', $doc->id) }}" loading="lazy" alt="" class="doc-thumb" onclick="hdvDocViewer.open({{ $doc->id }})">
        @else
            <div class="doc-icon" @if($canView) style="cursor:pointer" onclick="hdvDocViewer.open({{ $doc->id }})" @endif>{{ $isPdf ? '📕' : '📄' }}</div>
        @endif
        <div class="doc-info">
            <div class="doc-name">
                @if($canView)<a href="javascript:void(0)" onclick="hdvDocViewer.open({{ $doc->id }})" style="color:inherit;text-decoration:none;">{{ $doc->label }}</a>@else{{ $doc->label }}@endif
            </div>
            <div class="doc-meta">
                {{ $doc->file_name }} &middot; {{ $doc->file_size_formatted }}
                &middot; {{ $doc->uploader->name ?? '' }}
                &middot; {{ $doc->created_at->format('d/m/Y') }}
            </div>
            @if($doc->ai_verification_status)
            @php $aiColor = match($doc->ai_verification_status) { 'match' => '#10b981', 'mismatch', 'expired' => '#ef4444', default => '#94a3b8' }; @endphp
            <div style="font-size:.72rem;color:{{ $aiColor }};margin-top:.15rem;" title="{{ $doc->ai_verification_notes }}">
                🤖 {{ $doc->ai_verification_status_label }}@if($doc->ai_verification_notes) — {{ \Illuminate\Support\Str::limit($doc->ai_verification_notes, 80) }}@endif
            </div>
            @endif
            @if($doc->quality_status === 'warn')
            <div style="font-size:.72rem;color:#b45309;margin-top:.15rem;" title="{{ $doc->quality_notes }}">⚠ Calidad dudosa — {{ \Illuminate\Support\Str::limit($doc->quality_notes, 90) }}</div>
            @endif
            <div class="doc-reason" style="font-size:.75rem;color:var(--danger);margin-top:.15rem;{{ $doc->status === 'rejected' && $doc->rejection_reason ? '' : 'display:none;' }}">
                Motivo del rechazo: <span class="doc-reason-text">{{ $doc->rejection_reason }}</span>
            </div>
        </div>
        <span class="badge badge-{{ $badge }} doc-badge">{{ $doc->status_label }}</span>
        <div class="doc-actions">
            @if($canView)
            <button type="button" class="btn btn-sm btn-outline" title="Ver a pantalla completa" onclick="hdvDocViewer.open({{ $doc->id }})">👁 Ver</button>
            @endif
            <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline" title="Descargar">&#8615;</a>
            @if($catKey === 'comprobante_apartado' && ! $rental->apartado_paid_at)
            {{-- El "Verificar" genérico solo marca el documento como revisado, no
                 confirma el apartado ni genera el recibo — eso vive en la tarjeta
                 de Apartado en la pestaña Investigación (hallazgo 2026-09-24). --}}
            <a href="javascript:void(0)" onclick="switchTab('investigacion')" class="btn btn-sm btn-primary" title="Confirmar apartado">Confirmar apartado →</a>
            @else
            <button type="button" class="btn btn-sm btn-outline doc-approve" title="Aprobar" style="color:var(--success);{{ $doc->status === 'verified' ? 'display:none;' : '' }}" onclick="hdvDocViewer.setStatus({{ $doc->id }}, 'verified')">&#10003;</button>
            <button type="button" class="btn btn-sm btn-outline doc-reject" title="Rechazar" style="color:var(--danger);{{ $doc->status === 'rejected' ? 'display:none;' : '' }}" onclick="hdvDocViewer.open({{ $doc->id }}, true)">&#10007;</button>
            @endif
            <details class="doc-more">
                <summary class="btn btn-sm btn-outline" title="Más">&#8943;</summary>
                <div class="doc-more-menu">
                    <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" onsubmit="return confirm('¿Eliminar este documento? Esta acción no se puede deshacer.')">
                        @csrf @method('DELETE')
                        <button type="submit" style="color:var(--danger);">🗑 Eliminar</button>
                    </form>
                </div>
            </details>
        </div>
    </div>
    @endforeach
</div>
