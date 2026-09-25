@extends('layouts.app-sidebar')
@section('title', 'Documentos por revisar')

@section('styles')
.inbox-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; }
.inbox-tab { padding:.4rem .9rem; border:1px solid var(--border); border-radius:999px; font-size:.8rem; text-decoration:none; color:var(--text); background:var(--card); }
.inbox-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
.inbox-tab .n { opacity:.75; margin-left:.3rem; }
.inbox-group { margin-bottom:1.25rem; }
.inbox-head { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; margin-bottom:.5rem; }
.inbox-ctx { font-size:.72rem; color:var(--text-muted); }
.doc-item { display:flex; align-items:center; gap:.75rem; padding:.75rem 1rem; border:1px solid var(--border); border-radius:var(--radius); margin-bottom:.5rem; background:var(--card); }
.doc-icon { font-size:1.5rem; flex-shrink:0; }
.doc-info { flex:1; overflow:hidden; }
.doc-name { font-size:.85rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.doc-meta { font-size:.72rem; color:var(--text-muted); }
.doc-actions { display:flex; gap:.25rem; flex-shrink:0; }
.doc-item.doc-resolved { opacity:.5; }
.late-pill { background:#fef2f2; color:#991b1b; font-size:.68rem; font-weight:700; padding:.1rem .5rem; border-radius:999px; }
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <div>
        <h1 style="font-size:1.25rem;font-weight:700;margin:0;">Documentos por revisar</h1>
        <div style="font-size:.8rem;color:var(--text-muted);">Lo que subieron tus clientes y espera tu aprobación. Los más viejos primero.</div>
    </div>
    @if($counts['all'] > 0)
    <button type="button" class="btn btn-primary" onclick="hdvDocViewer.openFirstPending()">Revisar pendientes →</button>
    @endif
</div>

<form method="GET" class="inbox-tabs" style="align-items:center;">
    @php $tabs = ['all' => 'Todos', 'late' => 'Atrasados (+24 h)', 'renta' => 'Rentas', 'venta' => 'Ventas y expediente', 'captacion' => 'Captaciones']; @endphp
    @foreach($tabs as $key => $label)
        <a class="inbox-tab {{ $scope === $key ? 'active' : '' }}" href="{{ route('documents.inbox', array_filter(['scope' => $key === 'all' ? null : $key, 'q' => $q])) }}">{{ $label }}<span class="n">{{ $counts[$key] }}</span></a>
    @endforeach
    <input type="hidden" name="scope" value="{{ $scope }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Buscar cliente…" class="form-input" style="max-width:220px;margin-left:auto;">
</form>

@if($groups->isEmpty())
    <div class="card"><div class="card-body" style="text-align:center;padding:2.5rem 1rem;">
        <div style="font-size:2rem;">✅</div>
        <div style="font-weight:600;margin-top:.4rem;">Todo al día</div>
        <div style="font-size:.85rem;color:var(--text-muted);">No hay documentos esperando revisión{{ $scope !== 'all' || $q ? ' con este filtro' : '' }}.</div>
    </div></div>
@endif

@foreach($groups as $items)
    @php $client = $items->first()['doc']->client; @endphp
    <div class="inbox-group card"><div class="card-body" style="padding:1rem;">
        <div class="inbox-head">
            <strong style="font-size:.95rem;">{{ $client->name ?? 'Cliente' }}</strong>
            @if($client)<a href="{{ route('clients.show', $client->id) }}" style="font-size:.75rem;">ver ficha →</a>@endif
            <span class="badge badge-blue">{{ $items->count() }} por revisar</span>
            @foreach($items->pluck('ctx')->unique('label') as $ctx)
                @if($ctx['url'])<a class="inbox-ctx" href="{{ $ctx['url'] }}">{{ $ctx['label'] }} →</a>@else<span class="inbox-ctx">{{ $ctx['label'] }}</span>@endif
            @endforeach
        </div>
        @foreach($items as $item)
            @include('rentals._doc_row', ['doc' => $item['doc'], 'catKey' => $item['doc']->category, 'catLabel' => \App\Models\Document::CATEGORIES[$item['doc']->category] ?? $item['doc']->category, 'rental' => $item['doc']->rentalProcess, 'inbox' => true])
            @if($item['late'])<div style="margin:-.25rem 0 .5rem 3.25rem;"><span class="late-pill">⏰ Lleva más de 24 h sin revisar</span></div>@endif
        @endforeach
    </div></div>
@endforeach

@include('rentals._doc_viewer')
@endsection

@section('scripts')
<script>
// Al aprobar/rechazar en el visor la fila queda atenuada (no desaparece de golpe,
// para no descolocar la navegación) y baja el contador.
document.addEventListener('hdv:doc-status', function (e) {
    var row = document.getElementById('docrow-' + e.detail.id);
    if (row && e.detail.status !== 'received') { row.classList.add('doc-resolved'); }
});
</script>
@endsection
