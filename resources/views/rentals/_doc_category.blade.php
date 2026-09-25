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
        @include('rentals._doc_row', ['doc' => $doc, 'catKey' => $catKey, 'catLabel' => $catLabel, 'rental' => $rental ?? null])
    @endforeach
</div>
