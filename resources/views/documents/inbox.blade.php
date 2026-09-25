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
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('documents.metrics') }}" class="btn btn-outline">📊 Métricas</a>
        @if($counts['all'] > 0)
        <button type="button" class="btn btn-primary" onclick="hdvDocViewer.openFirstPending()">Revisar pendientes →</button>
        @endif
    </div>
</div>

<form method="GET" class="inbox-tabs" style="align-items:center;">
    @php $tabs = ['all' => 'Todos', 'late' => 'Atrasados (+24 h)', 'renta' => 'Rentas', 'venta' => 'Ventas y expediente', 'captacion' => 'Captaciones']; @endphp
    @foreach($tabs as $key => $label)
        <a class="inbox-tab {{ $scope === $key ? 'active' : '' }}" href="{{ route('documents.inbox', array_filter(['scope' => $key === 'all' ? null : $key, 'q' => $q])) }}">{{ $label }}<span class="n">{{ $counts[$key] }}</span></a>
    @endforeach
    <input type="hidden" name="scope" value="{{ $scope }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Buscar cliente…" class="form-input" style="max-width:220px;margin-left:auto;">
</form>

@if($groups->isNotEmpty())
<div id="bulkBar" class="card" style="position:sticky;top:0;z-index:30;margin-bottom:1rem;box-shadow:0 2px 8px rgba(0,0,0,.06);">
    <div class="card-body" style="padding:.6rem 1rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;cursor:pointer;"><input type="checkbox" id="selAll" style="width:16px;height:16px;"> Seleccionar todos</label>
        <button type="button" class="btn btn-sm btn-outline" id="btnSelAi" title="Selecciona los que la verificación automática marcó como coincide y no tienen aviso de calidad">🤖 Seleccionar los que coinciden</button>
        <span id="bulkCount" style="font-size:.8rem;color:var(--text-muted);">0 seleccionados</span>
        <button type="button" class="btn btn-sm btn-primary" id="btnBulk" disabled style="margin-left:auto;">✓ Aprobar seleccionados</button>
    </div>
</div>
@endif

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
// ── Aprobación en bloque ────────────────────────────────────────────────────
(function () {
    var csrf = '{{ csrf_token() }}';
    function boxes() { return Array.prototype.slice.call(document.querySelectorAll('.doc-select')).filter(function (b) { var r = b.closest('.doc-item'); return r && !r.classList.contains('doc-resolved'); }); }
    function selected() { return boxes().filter(function (b) { return b.checked; }); }
    function refresh() {
        var n = selected().length;
        document.getElementById('bulkCount').textContent = n + (n === 1 ? ' seleccionado' : ' seleccionados');
        var btn = document.getElementById('btnBulk'); btn.disabled = n === 0;
        btn.textContent = n ? '✓ Aprobar ' + n + (n === 1 ? ' documento' : ' documentos') : '✓ Aprobar seleccionados';
    }
    var all = document.getElementById('selAll'); if (!all) return;
    all.addEventListener('change', function () { boxes().forEach(function (b) { b.checked = all.checked; }); refresh(); });
    document.addEventListener('change', function (e) { if (e.target.classList && e.target.classList.contains('doc-select')) refresh(); });
    document.getElementById('btnSelAi').addEventListener('click', function () {
        boxes().forEach(function (b) { var r = b.closest('.doc-item'); b.checked = r.dataset.aiStatus === 'match' && !r.dataset.quality; });
        refresh();
    });
    document.getElementById('btnBulk').addEventListener('click', function () {
        var ids = selected().map(function (b) { return parseInt(b.value, 10); });
        if (!ids.length || !confirm('¿Aprobar ' + ids.length + ' documento(s)? Asegúrate de haberlos revisado (puedes abrirlos con 👁 Ver).')) return;
        var btn = this; btn.disabled = true; btn.textContent = 'Aprobando…';
        var body = new FormData(); ids.forEach(function (id) { body.append('ids[]', id); });
        fetch("{{ route('documents.bulk-approve') }}", { method: 'POST', body: body, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
            .then(function (j) {
                ids.forEach(function (id) {
                    if (j.skipped && j.skipped[id]) return;
                    var row = document.getElementById('docrow-' + id); if (!row) return;
                    row.classList.add('doc-resolved'); row.dataset.status = 'verified';
                    var b = row.querySelector('.doc-badge'); if (b) { b.className = 'badge badge-green doc-badge'; b.textContent = 'Verificado'; }
                    var cb = row.querySelector('.doc-select'); if (cb) { cb.checked = false; cb.disabled = true; }
                });
                var skipped = Object.keys(j.skipped || {});
                alert(j.approved + ' documento(s) aprobados.' + (skipped.length ? '\n' + skipped.length + ' se omitieron: ' + Object.values(j.skipped).filter(function (v, i, a) { return a.indexOf(v) === i; }).join('; ') + '.' : ''));
                all.checked = false; refresh();
            })
            .catch(function () { alert('No se pudo aprobar en bloque. Intenta de nuevo.'); btn.disabled = false; refresh(); });
    });
    refresh();
})();

document.addEventListener('hdv:doc-status', function (e) {
    var row = document.getElementById('docrow-' + e.detail.id);
    if (row && e.detail.status !== 'received') { row.classList.add('doc-resolved'); }
});
</script>
@endsection
