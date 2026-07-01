@extends('layouts.app-sidebar')
@section('title', 'Pipeline de Captación')

@section('styles')
{{-- @yield('styles') ya está DENTRO del <style> global del layout.
     NO agregar tags <style> aquí — rompe el CSS parser del browser. --}}
/* ===== STATS STRIP ===== */
.stats-strip {
    display: flex; flex-direction: row; flex-wrap: nowrap; gap: 0.65rem;
    margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 0.25rem;
}
.mini-stat {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.55rem 0.85rem; background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); white-space: nowrap; min-width: max-content;
}
.mini-stat-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.mini-stat-val   { font-size: 1.05rem; font-weight: 700; color: var(--text); line-height: 1; }
.mini-stat-label { font-size: 0.68rem; color: var(--text-muted); }

/* ===== FILTER BAR ===== */
.filter-bar {
    display: flex; gap: 0.5rem; align-items: center;
    margin-bottom: 1rem; flex-wrap: wrap;
}
.filter-bar .form-control,
.filter-bar .form-select {
    font-size: 0.82rem; padding: 0.35rem 0.65rem;
}
.filter-bar .search-input { min-width: 200px; flex: 1; max-width: 320px; }

/* ===== KANBAN ===== */
.kanban-wrap { display: flex; gap: 0.6rem; overflow-x: auto; padding-bottom: 1rem; -webkit-overflow-scrolling: touch; }
.kanban-col { min-width: 280px; max-width: 280px; flex-shrink: 0; display: flex; flex-direction: column; max-height: calc(100vh - 260px); }
.kanban-col-header { padding: 0.55rem 0.75rem; display: flex; justify-content: space-between; align-items: center; background: var(--card); border: 1px solid var(--border); border-bottom: none; border-radius: var(--radius) var(--radius) 0 0; position: relative; }
.kanban-col-header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--radius) var(--radius) 0 0; }
.kanban-col-title { font-size: 0.78rem; font-weight: 700; color: var(--text); text-transform: uppercase; letter-spacing: 0.03em; }
.kanban-col-count { font-size: 0.68rem; font-weight: 600; padding: 0.1rem 0.45rem; border-radius: 10px; background: var(--bg); color: var(--text-muted); }
.kanban-col-body { background: var(--bg); border: 1px solid var(--border); border-radius: 0 0 var(--radius) var(--radius); flex: 1; overflow-y: auto; padding: 0.5rem; display: flex; flex-direction: column; gap: 0.4rem; }
.kanban-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.65rem 0.75rem; cursor: pointer; transition: box-shadow 0.15s, border-color 0.15s; border-left-width: 3px; }
.kanban-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-color: var(--primary); }
.kanban-card-name { font-size: 0.82rem; font-weight: 600; color: var(--text); margin-bottom: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kanban-card-address { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.35rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kanban-card-meta { display: flex; justify-content: space-between; align-items: center; gap: 0.35rem; }
.kanban-card-badge { font-size: 0.67rem; padding: 0.1rem 0.45rem; border-radius: 20px; font-weight: 600; }
.kanban-card-actions { display: flex; gap: 0.25rem; margin-top: 0.45rem; flex-wrap: wrap; }
.kanban-card-actions .btn { font-size: 0.7rem; padding: 0.2rem 0.5rem; }

/* Avatar */
.user-avatar {
    width: 24px; height: 24px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.62rem; font-weight: 700; color: #fff;
    background: var(--primary); flex-shrink: 0;
}

/* Days badge */
.days-badge {
    font-size: 0.67rem; padding: 0.1rem 0.4rem; border-radius: 20px;
    font-weight: 600; background: var(--bg); color: var(--text-muted);
    border: 1px solid var(--border);
}
.days-badge.stale { background: rgba(239,68,68,.1); color: #ef4444; border-color: rgba(239,68,68,.3); }

/* Move dropdown */
.move-dropdown { position: relative; }
.move-dropdown-menu {
    position: absolute; bottom: calc(100% + 4px); left: 0; right: 0;
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 0.25rem 0;
    z-index: 100; box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    display: none; min-width: 160px;
}
.move-dropdown-menu.open { display: block; }
.move-dropdown-item {
    display: block; width: 100%; padding: 0.3rem 0.65rem;
    font-size: 0.75rem; color: var(--text); text-align: left;
    background: none; border: none; cursor: pointer;
    transition: background 0.1s;
}
.move-dropdown-item:hover { background: var(--bg); }

/* Empty column */
.kanban-empty { text-align: center; color: var(--text-muted); font-size: 0.75rem; padding: 1.5rem 0.5rem; opacity: 0.7; }

/* FAB */
.fab-btn {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    width: 52px; height: 52px; border-radius: 50%;
    background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    text-decoration: none; z-index: 50;
    transition: transform 0.15s, box-shadow 0.15s;
}
.fab-btn:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(0,0,0,0.25); color: #fff; }

@media (max-width: 768px) {
    .kanban-col { min-width: 250px; max-width: 250px; }
    .stats-strip { display: none; }
    .filter-bar .search-input { max-width: 100%; }
}
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2>Pipeline de Captación</h2>
        <p style="color:var(--text-muted);font-size:.82rem;">Propietarios en proceso de firma de exclusiva</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <a href="{{ route('admin.captaciones.create-from-call') }}" class="btn btn-primary btn-sm">
            + Nueva captación
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1rem;">{{ session('error') }}</div>
@endif

{{-- ===== STATS STRIP ===== --}}
<div class="stats-strip">
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:rgba(59,130,246,.1);color:#3b82f6;">&#127968;</div>
        <div>
            <div class="mini-stat-val">{{ $stats['total'] }}</div>
            <div class="mini-stat-label">Total en pipeline</div>
        </div>
    </div>
    <div class="mini-stat" style="border-color:rgba(34,197,94,.3);">
        <div class="mini-stat-icon" style="background:rgba(34,197,94,.1);color:#22c55e;">&#10003;</div>
        <div>
            <div class="mini-stat-val" style="color:#22c55e;">{{ $stats['converted_this_month'] }}</div>
            <div class="mini-stat-label">Convertidos este mes</div>
        </div>
    </div>
    <div class="mini-stat" style="border-color:rgba(239,68,68,.3);">
        <div class="mini-stat-icon" style="background:rgba(239,68,68,.1);color:#ef4444;">&#10005;</div>
        <div>
            <div class="mini-stat-val" style="color:#ef4444;">{{ $stats['declined_this_month'] }}</div>
            <div class="mini-stat-label">Declinados este mes</div>
        </div>
    </div>
    @foreach($stages as $stageKey => $stageLabel)
    @php $colCount = ($byStage[$stageKey] ?? collect())->count(); @endphp
    @if($colCount > 0)
    <div class="mini-stat">
        <div class="mini-stat-icon" style="background:{{ $stageColors[$stageKey] }}22;color:{{ $stageColors[$stageKey] }};font-size:.65rem;font-weight:800;">{{ mb_strtoupper(mb_substr($stageKey,0,2)) }}</div>
        <div>
            <div class="mini-stat-val">{{ $colCount }}</div>
            <div class="mini-stat-label">{{ $stageLabel }}</div>
        </div>
    </div>
    @endif
    @endforeach
</div>

{{-- ===== FILTER BAR ===== --}}
<form method="GET" action="{{ request()->url() }}" class="filter-bar" id="filterForm">
    <input
        type="text"
        name="search"
        class="form-control search-input"
        placeholder="Buscar por cliente…"
        value="{{ request('search') }}"
        oninput="debounceSubmit()"
    >
    @if(count($users) > 0)
    <select name="user_id" class="form-select" style="min-width:160px;" onchange="this.form.submit()">
        <option value="">— Todos los asesores —</option>
        @foreach($users as $user)
        <option value="{{ $user->id }}" @selected($currentUser == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
    @endif
    @if(request('search') || request('user_id'))
    <a href="{{ request()->url() }}" class="btn btn-sm" style="white-space:nowrap;">&#10005; Limpiar</a>
    @endif
</form>

{{-- ===== KANBAN BOARD ===== --}}
<div class="kanban-wrap" id="kanbanWrap">
    @foreach($stages as $stageKey => $stageLabel)
    @php
        $color = $stageColors[$stageKey];
        $ops   = $byStage[$stageKey] ?? collect();
        $stageKeys = array_keys($stages);
        $currentIdx = array_search($stageKey, $stageKeys);
        $nextStage = $stageKeys[$currentIdx + 1] ?? null;
        $nextLabel = $nextStage ? $stages[$nextStage] : null;
    @endphp
    <div class="kanban-col" data-stage="{{ $stageKey }}">
        {{-- Column Header --}}
        <div class="kanban-col-header" style="--col-color:{{ $color }};">
            <style>
                .kanban-col[data-stage="{{ $stageKey }}"] .kanban-col-header::before { background: {{ $color }}; }
            </style>
            <span class="kanban-col-title">{{ $stageLabel }}</span>
            <span class="kanban-col-count">{{ $ops->count() }}</span>
        </div>

        {{-- Column Body --}}
        <div class="kanban-col-body">
            @forelse($ops as $op)
            @php
                $client   = $op->client;
                $property = $op->property ?? null;

                $clientName = $client->name ?? '(Sin nombre)';
                $address    = $property->address ?? (isset($op->notes) ? \Illuminate\Support\Str::limit($op->notes, 45) : '—');

                $intent     = $op->intent ?? $op->target_type ?? null;
                $intentLabel = match($intent) {
                    'venta','sale'    => 'Venta',
                    'renta','rental'  => 'Renta',
                    default           => $intent ? ucfirst($intent) : null,
                };
                $intentColor = match($intent) {
                    'venta','sale'   => '#3b82f6',
                    'renta','rental' => '#a78bfa',
                    default          => '#94a3b8',
                };

                $daysInStage = (int) \Carbon\Carbon::parse($op->created_at)->diffInDays(now());
                $isStale     = $daysInStage > 14;

                $assignedUser = $op->user ?? null;
                $userInitials = $assignedUser
                    ? collect(explode(' ', $assignedUser->name))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('')
                    : '?';

                $captacionId  = $op->captacion_id ?? null;
                $captacion    = $captacionId ? ($op->captacion ?? null) : null;

                $phone = $client->phone ?? $client->whatsapp ?? null;
            @endphp

            <div class="kanban-card" style="border-left-color:{{ $color }};" data-op-id="{{ $op->id }}">
                {{-- Name --}}
                <div class="kanban-card-name" title="{{ $clientName }}">
                    @if($captacionId)
                        <a href="{{ route('admin.captaciones.show', $captacionId) }}" style="color:inherit;text-decoration:none;">{{ $clientName }}</a>
                    @else
                        {{ $clientName }}
                    @endif
                </div>

                {{-- Address --}}
                @if($address && $address !== '—')
                <div class="kanban-card-address" title="{{ $address }}">{{ $address }}</div>
                @endif

                {{-- Meta row: intent badge + days + user avatar --}}
                <div class="kanban-card-meta">
                    <div style="display:flex;align-items:center;gap:.3rem;">
                        @if($intentLabel)
                        <span class="kanban-card-badge" style="background:{{ $intentColor }}22;color:{{ $intentColor }};border:1px solid {{ $intentColor }}44;">
                            {{ $intentLabel }}
                        </span>
                        @endif
                        <span class="days-badge {{ $isStale ? 'stale' : '' }}" title="Días en etapa">
                            {{ $daysInStage }}d
                        </span>
                    </div>
                    <div class="user-avatar" title="{{ $assignedUser?->name ?? 'Sin asignar' }}">
                        {{ $userInitials }}
                    </div>
                </div>

                {{-- Contextual action buttons --}}
                <div class="kanban-card-actions">
                    @if($stageKey === 'lead')
                        @if($phone)
                        <a href="tel:{{ $phone }}" class="btn btn-sm" title="Llamar a {{ $clientName }}">&#128222; Llamar</a>
                        @endif
                        <a href="{{ route('admin.captaciones.create-from-call') }}{{ $client ? '?client_id='.$client->id : '' }}"
                           class="btn btn-sm btn-primary">Crear captación</a>

                    @elseif($stageKey === 'contacto')
                        @if($captacionId)
                        <a href="{{ route('admin.captaciones.presentation', $captacionId) }}" class="btn btn-sm">&#128196; Presentación</a>
                        @else
                        <button class="btn btn-sm" disabled title="Requiere captación vinculada">&#128196; Presentación</button>
                        @endif

                    @elseif($stageKey === 'visita')
                        @if($captacionId)
                        <a href="{{ route('admin.captaciones.show', $captacionId) }}#agenda" class="btn btn-sm">&#128197; Visita</a>
                        @else
                        <a href="{{ route('operations.show', $op->id) }}" class="btn btn-sm">&#128197; Ver</a>
                        @endif

                    @elseif($stageKey === 'avaluo')
                        @if($captacionId)
                        <a href="{{ route('admin.captaciones.show', $captacionId) }}#valuacion" class="btn btn-sm">&#128202; Valuación</a>
                        @else
                        <button class="btn btn-sm" disabled>&#128202; Valuación</button>
                        @endif

                    @elseif($stageKey === 'exclusiva')
                        @if($captacionId)
                        <form method="POST" action="{{ route('admin.captaciones.generar-exclusiva', $captacionId) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">&#9997;&#65039; Exclusiva</button>
                        </form>
                        @else
                        <button class="btn btn-sm" disabled>&#9997;&#65039; Exclusiva</button>
                        @endif

                    @else
                        {{-- Generic: just the "Ver" link as primary action --}}
                        @if($captacionId)
                        <a href="{{ route('admin.captaciones.show', $captacionId) }}" class="btn btn-sm">Ver detalles</a>
                        @endif
                    @endif

                    {{-- Always: small "Ver →" link --}}
                    @if($captacionId)
                    <a href="{{ route('admin.captaciones.show', $captacionId) }}" class="btn btn-sm" style="margin-left:auto;" title="Ver captación">Ver &#8594;</a>
                    @endif
                </div>

                {{-- Move to next stage --}}
                @if($nextStage)
                <div class="move-dropdown" style="margin-top:0.4rem;">
                    <button
                        type="button"
                        class="btn btn-sm"
                        style="width:100%;font-size:0.68rem;color:var(--text-muted);"
                        onclick="toggleMoveMenu(this)"
                    >Mover &#8594;</button>
                    <div class="move-dropdown-menu">
                        @foreach(array_slice($stageKeys, $currentIdx + 1) as $targetStage)
                        <form method="POST" action="{{ route('operations.update-stage', $op->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="stage" value="{{ $targetStage }}">
                            <button type="submit" class="move-dropdown-item">
                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $stageColors[$targetStage] }};margin-right:6px;vertical-align:middle;"></span>
                                {{ $stages[$targetStage] }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="kanban-empty">
                <div style="font-size:1.3rem;margin-bottom:.4rem;">&#128269;</div>
                Sin registros
            </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- FAB --}}
<a href="{{ route('admin.captaciones.create-from-call') }}" class="fab-btn" title="Nueva captación">+</a>

@endsection

@section('scripts')
<script>
// ── Filter debounce ──────────────────────────────────────────────────────────
let _filterTimer;
function debounceSubmit() {
    clearTimeout(_filterTimer);
    _filterTimer = setTimeout(() => document.getElementById('filterForm').submit(), 450);
}

// ── Move dropdown ────────────────────────────────────────────────────────────
function toggleMoveMenu(btn) {
    const menu = btn.nextElementSibling;
    const isOpen = menu.classList.contains('open');
    // Close all open menus first
    document.querySelectorAll('.move-dropdown-menu.open').forEach(m => m.classList.remove('open'));
    if (!isOpen) menu.classList.add('open');
}

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.move-dropdown')) {
        document.querySelectorAll('.move-dropdown-menu.open').forEach(m => m.classList.remove('open'));
    }
});

// ── Client-side search filter (instant, no reload) ──────────────────────────
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        document.querySelectorAll('.kanban-card').forEach(card => {
            const name = card.querySelector('.kanban-card-name')?.textContent.toLowerCase() ?? '';
            card.style.display = (!q || name.includes(q)) ? '' : 'none';
        });
        // Update column counts
        document.querySelectorAll('.kanban-col').forEach(col => {
            const visible = col.querySelectorAll('.kanban-card:not([style*="display: none"])').length;
            const countEl = col.querySelector('.kanban-col-count');
            if (countEl) countEl.textContent = visible;
        });
    });
}
</script>
@endsection
