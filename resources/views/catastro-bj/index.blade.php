@extends('layouts.app-sidebar')
@section('title', 'Catastro y Zonificación BJ')

@section('content')
<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-outline { background:#fff; color:var(--text); border:1px solid var(--border); }
.table-card { background:#fff; border-radius:12px; border:1px solid var(--border); overflow:hidden; }
table { width:100%; border-collapse:collapse; }
thead th { padding:.7rem 1rem; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); border-bottom:1px solid var(--border); text-align:left; background:#fafafa; white-space:nowrap; }
tbody td { padding:.7rem 1rem; font-size:.83rem; border-bottom:1px solid var(--border); vertical-align:middle; }
tbody tr:last-child td { border-bottom:none; }
.badge { display:inline-block; padding:.15rem .55rem; border-radius:999px; font-size:.72rem; font-weight:600; background:#eff6ff; color:#1d4ed8; }
.filter-tabs { display:flex; gap:.4rem; margin-bottom:1.25rem; }
.filter-tab { padding:.35rem .85rem; border-radius:8px; font-size:.8rem; font-weight:600; cursor:pointer; border:1px solid var(--border); background:#fff; color:var(--text-muted); text-decoration:none; }
.filter-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
.filter-form { display:flex; gap:.6rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.25rem; background:#fff; border:1px solid var(--border); border-radius:12px; padding:1rem 1.1rem; }
.filter-field { display:flex; flex-direction:column; gap:.3rem; }
.filter-field label { font-size:.72rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.03em; }
.filter-field input, .filter-field select { border:1px solid var(--border); border-radius:8px; padding:.5rem .65rem; font-size:.83rem; min-width:150px; }
.stats-row { display:flex; gap:.75rem; margin-bottom:1.25rem; }
.stat-chip { background:#fff; border:1px solid var(--border); border-radius:10px; padding:.6rem 1rem; font-size:.8rem; color:var(--text-muted); }
.stat-chip strong { color:var(--text); font-size:.95rem; }
.empty { text-align:center; padding:3rem 2rem; color:var(--text-muted); }
</style>

<div class="page-header">
    <div>
        <h2 style="margin:0;">Catastro y Zonificación — Benito Juárez</h2>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">Bases públicas de SEDUVI, independientes de tu cartera — para armar listas de predios candidatos</p>
    </div>
    @if($tab !== 'catastro')
    <a href="{{ route('catastro-bj.export', request()->query()) }}" class="btn btn-primary">⬇ Exportar CSV (filtro actual)</a>
    @endif
</div>

<div class="stats-row">
    <div class="stat-chip"><strong>{{ number_format($stats['total_zonificacion']) }}</strong> predios en zonificación</div>
    <div class="stat-chip"><strong>{{ number_format($stats['total_catastro']) }}</strong> predios en catastro</div>
</div>

<div class="filter-tabs">
    <a href="{{ route('catastro-bj.index', ['tab' => 'zonificacion']) }}" class="filter-tab {{ $tab !== 'catastro' ? 'active' : '' }}">Zonificación (uso de suelo / niveles)</a>
    <a href="{{ route('catastro-bj.index', ['tab' => 'catastro']) }}" class="filter-tab {{ $tab === 'catastro' ? 'active' : '' }}">Catastro (terreno / construcción)</a>
</div>

@if($tab === 'catastro')
<form method="GET" class="filter-form">
    <input type="hidden" name="tab" value="catastro">
    <div class="filter-field">
        <label>Colonia</label>
        <input type="text" name="colonia" value="{{ request('colonia') }}" placeholder="Ej. Del Valle Centro">
    </div>
    <div class="filter-field">
        <label>Calle</label>
        <input type="text" name="calle" value="{{ request('calle') }}" placeholder="Ej. Insurgentes">
    </div>
    <div class="filter-field">
        <label>Terreno mínimo (m²)</label>
        <input type="number" name="sup_terreno_min" value="{{ request('sup_terreno_min') }}" placeholder="Ej. 300">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
    @if(request()->hasAny(['colonia','calle','sup_terreno_min']))
    <a href="{{ route('catastro-bj.index', ['tab' => 'catastro']) }}" class="btn btn-outline">Limpiar</a>
    @endif
</form>

<div class="table-card">
    @if($catastro->count())
    <table>
        <thead>
            <tr>
                <th>Calle</th><th>No.</th><th>Colonia</th><th>C.P.</th>
                <th>Terreno m²</th><th>Construcción m²</th><th>Año</th>
            </tr>
        </thead>
        <tbody>
            @foreach($catastro as $c)
            <tr>
                <td>{{ $c->calle ?: '—' }}</td>
                <td>{{ $c->numero ?: '—' }}</td>
                <td>{{ $c->colonia ?: '—' }}</td>
                <td>{{ $c->codigo_postal ?: '—' }}</td>
                <td>{{ $c->sup_terreno ? number_format($c->sup_terreno, 0) : '—' }}</td>
                <td>{{ $c->sup_construccion ? number_format($c->sup_construccion, 0) : '—' }}</td>
                <td>{{ $c->anio_construccion ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding:1rem 1.2rem;">{{ $catastro->links() }}</div>
    @else
    <div class="empty">Sin resultados con esos filtros.</div>
    @endif
</div>

@else
<form method="GET" class="filter-form">
    <input type="hidden" name="tab" value="zonificacion">
    <div class="filter-field">
        <label>Colonia</label>
        <input type="text" name="colonia" value="{{ request('colonia') }}" placeholder="Ej. Del Valle Norte">
    </div>
    <div class="filter-field">
        <label>Calle</label>
        <input type="text" name="calle" value="{{ request('calle') }}" placeholder="Ej. Insurgentes">
    </div>
    <div class="filter-field">
        <label>Niveles permitidos</label>
        <select name="niveles">
            <option value="">Todos</option>
            @foreach($nivelesDisponibles as $n)
                <option value="{{ $n }}" {{ request('niveles') == $n ? 'selected' : '' }}>H{{ $n }} ({{ $n }} niveles)</option>
            @endforeach
        </select>
    </div>
    <div class="filter-field">
        <label>Superficie mínima (m²)</label>
        <input type="number" name="superficie_min" value="{{ request('superficie_min') }}" placeholder="Ej. 300">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
    @if(request()->hasAny(['colonia','calle','niveles','superficie_min']))
    <a href="{{ route('catastro-bj.index', ['tab' => 'zonificacion']) }}" class="btn btn-outline">Limpiar</a>
    @endif
</form>

<div class="table-card">
    @if($zonificacion->count())
    <table>
        <thead>
            <tr>
                <th>Calle</th><th>No.</th><th>Colonia</th><th>C.P.</th>
                <th>Superficie m²</th><th>Niveles</th><th>Uso de suelo</th><th>Mapa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($zonificacion as $z)
            <tr>
                <td>{{ $z->calle ?: '—' }}</td>
                <td>{{ $z->no_externo ?: '—' }}</td>
                <td>{{ $z->colonia ?: '—' }}</td>
                <td>{{ $z->codigo_postal ?: '—' }}</td>
                <td>{{ $z->superficie ? number_format($z->superficie, 0) : '—' }}</td>
                <td>@if($z->niveles)<span class="badge">H{{ $z->niveles }}</span>@else — @endif</td>
                <td>{{ $z->uso_descri ?: '—' }}</td>
                <td>
                    @if($z->latitud && $z->longitud)
                    <a href="https://www.google.com/maps?q={{ $z->latitud }},{{ $z->longitud }}" target="_blank" style="color:var(--primary);">Ver</a>
                    @else — @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding:1rem 1.2rem;">{{ $zonificacion->links() }}</div>
    @else
    <div class="empty">Sin resultados con esos filtros.</div>
    @endif
</div>
@endif
@endsection
