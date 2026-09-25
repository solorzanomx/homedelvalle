@extends('layouts.app-sidebar')
@section('title', 'Métricas de documentos')

@section('styles')
.mt-tiles { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:.75rem; margin-bottom:1.25rem; }
.mt-tile { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:.85rem 1rem; }
.mt-tile .v { font-size:1.5rem; font-weight:700; line-height:1.1; }
.mt-tile .l { font-size:.72rem; color:var(--text-muted); margin-top:.15rem; }
.mt-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:1rem; }
.mt-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.mt-table th { text-align:left; font-size:.68rem; text-transform:uppercase; color:var(--text-muted); padding:.35rem .4rem; }
.mt-table td { padding:.4rem; border-top:1px solid var(--border); }
.mt-bar { height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden; min-width:60px; }
.mt-bar > span { display:block; height:100%; background:#ef4444; }
@endsection

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
    <div>
        <a href="{{ route('documents.inbox') }}" style="font-size:.78rem;">← Docs por revisar</a>
        <h1 style="font-size:1.25rem;font-weight:700;margin:.15rem 0 0;">Métricas de documentos</h1>
        <div style="font-size:.8rem;color:var(--text-muted);">Qué se sube, qué se rechaza y por qué. Sirve para afinar las guías del Portal y los umbrales de calidad.</div>
    </div>
    <div style="display:flex;gap:.4rem;">
        @foreach([7, 30, 90] as $d)
            <a class="btn btn-sm {{ $days === $d ? 'btn-primary' : 'btn-outline' }}" href="{{ route('documents.metrics', ['days' => $d]) }}">{{ $d }} días</a>
        @endforeach
    </div>
</div>

<div class="mt-tiles">
    <div class="mt-tile"><div class="v">{{ $kpis['uploads'] }}</div><div class="l">Documentos subidos</div></div>
    <div class="mt-tile"><div class="v" style="color:#16a34a;">{{ $kpis['verified'] }}</div><div class="l">Aprobados</div></div>
    <div class="mt-tile"><div class="v" style="color:#dc2626;">{{ $kpis['rejected'] }}</div><div class="l">Rechazados ({{ $kpis['rate'] }}% de lo subido)</div></div>
    <div class="mt-tile"><div class="v">{{ $kpis['median_hours'] !== null ? $kpis['median_hours'] . ' h' : '—' }}</div><div class="l">Tiempo típico hasta la revisión</div></div>
    <div class="mt-tile"><div class="v">{{ $kpis['blocks'] }}</div><div class="l">Bloqueados al subir (calidad)</div></div>
    <div class="mt-tile"><div class="v">{{ $kpis['bypassed'] }}</div><div class="l">Pasaron tras 2 bloqueos</div></div>
</div>

<div class="mt-grid">
    <div class="card"><div class="card-body" style="padding:1rem;">
        <h3 style="font-size:.9rem;margin:0 0 .5rem;">Por tipo de documento</h3>
        @if($perCategory->isEmpty())<div style="font-size:.82rem;color:var(--text-muted);">Aún no hay datos en este periodo.</div>@else
        <table class="mt-table"><thead><tr><th>Documento</th><th>Subidos</th><th>Rechazados</th><th style="width:90px;">% rechazo</th><th>⚠ Calidad</th></tr></thead><tbody>
        @foreach($perCategory as $r)
            <tr><td>{{ $r['label'] }}</td><td>{{ $r['uploads'] }}</td><td>{{ $r['rejected'] }}</td>
                <td><div class="mt-bar"><span style="width:{{ min(100, $r['rate']) }}%"></span></div> <small>{{ $r['rate'] }}%</small></td><td>{{ $r['warned'] }}</td></tr>
        @endforeach
        </tbody></table>
        <div style="font-size:.7rem;color:var(--text-muted);margin-top:.5rem;">Un % alto en un tipo suele significar que su guía del Portal no es clara.</div>
        @endif
    </div></div>

    <div class="card"><div class="card-body" style="padding:1rem;">
        <h3 style="font-size:.9rem;margin:0 0 .5rem;">Motivos de rechazo más usados</h3>
        @forelse($reasons as $text => $n)
            <div style="display:flex;gap:.6rem;font-size:.82rem;padding:.3rem 0;border-top:1px solid var(--border);"><strong style="min-width:1.6rem;">{{ $n }}</strong><span>{{ \Illuminate\Support\Str::limit($text, 110) }}</span></div>
        @empty<div style="font-size:.82rem;color:var(--text-muted);">Sin rechazos en este periodo.</div>@endforelse
    </div></div>

    <div class="card"><div class="card-body" style="padding:1rem;">
        <h3 style="font-size:.9rem;margin:0 0 .5rem;">Bloqueos automáticos al subir</h3>
        @forelse($blocksByCat as $label => $n)
            <div style="display:flex;gap:.6rem;font-size:.82rem;padding:.3rem 0;border-top:1px solid var(--border);"><strong style="min-width:1.6rem;">{{ $n }}</strong><span>{{ $label }}</span></div>
        @empty<div style="font-size:.82rem;color:var(--text-muted);">Nada bloqueado en este periodo.</div>@endforelse
        @if($blockReasons->isNotEmpty())
        <div style="font-size:.72rem;font-weight:700;margin:.8rem 0 .3rem;">Razones</div>
        @foreach($blockReasons as $text => $n)
            <div style="font-size:.78rem;color:var(--text-muted);">{{ $n }} × {{ $text }}…</div>
        @endforeach
        <div style="font-size:.7rem;color:var(--text-muted);margin-top:.5rem;">Si ves muchos bloqueos con una razón dudosa (p. ej. "no se alcanza a leer"), avísale a quien mantiene el sistema: puede que el umbral esté muy estricto.</div>
        @endif
    </div></div>
</div>
@endsection
