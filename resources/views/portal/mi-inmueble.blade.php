@extends('layouts.portal')
@section('title', 'Mi Inmueble')

@section('styles')
/* ── Mi Inmueble ───────────────────────────────────────── */
.mi-inmueble-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.mi-stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem 1rem;
    text-align: center;
}
.mi-stat-val {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0E304B;
    line-height: 1;
    margin-bottom: .35rem;
}
.mi-stat-lbl {
    font-size: .72rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
}

/* ── Pulse bars ──────────────────────────────────────────── */
.pulse-bars {
    display: flex;
    align-items: flex-end;
    gap: .75rem;
    height: 100px;
    margin: 1rem 0 .5rem;
}
.pulse-bar-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .3rem;
    height: 100%;
    justify-content: flex-end;
}
.pulse-bar {
    width: 100%;
    background: #1D4ED8;
    border-radius: 4px 4px 0 0;
    min-height: 4px;
    transition: height .4s ease;
}
.pulse-bar-label {
    font-size: .65rem;
    color: var(--text-muted);
    font-weight: 600;
    white-space: nowrap;
}
.pulse-bar-count {
    font-size: .7rem;
    font-weight: 700;
    color: #0E304B;
}

/* ── Reaction summary ─────────────────────────────────── */
.reaction-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .6rem 0;
    border-bottom: 1px solid var(--border);
    font-size: .85rem;
}
.reaction-row:last-child { border-bottom: none; }

/* ── Visit timeline ───────────────────────────────────── */
.visit-item {
    display: flex;
    gap: .75rem;
    padding: .75rem 0;
    border-bottom: 1px solid var(--border);
    align-items: flex-start;
}
.visit-item:last-child { border-bottom: none; }
.visit-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-top: .3rem;
    flex-shrink: 0;
}
.visit-info { flex: 1; min-width: 0; }
.visit-date { font-size: .82rem; font-weight: 600; color: var(--text); }
.visit-meta { font-size: .72rem; color: var(--text-muted); margin-top: 2px; }

/* ── Comment card ─────────────────────────────────────── */
.comment-card {
    background: var(--bg);
    border-radius: var(--radius);
    padding: .75rem 1rem;
    margin-bottom: .6rem;
    font-size: .82rem;
    color: var(--text);
    line-height: 1.5;
}
.comment-meta {
    font-size: .7rem;
    color: var(--text-muted);
    margin-top: .3rem;
}

/* ── Progress bar ─────────────────────────────────────── */
.prog-bar-bg {
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
    margin-top: .4rem;
}
.prog-bar-fill {
    height: 100%;
    background: #22C55E;
    border-radius: 4px;
    transition: width .4s ease;
}
@endsection

@section('content')

{{-- Page header --}}
<div class="page-header">
    <div>
        <h2>Mi Inmueble</h2>
        @if(isset($property) && $property)
        <p class="text-muted" style="font-size:.85rem;">
            {{ $property->address }}@if($property->colony), {{ $property->colony }}@endif
        </p>
        @endif
    </div>
</div>

{{-- Empty state --}}
@if(!isset($property) || !$property)
<div class="card">
    <div class="card-body" style="text-align:center;padding:3rem;">
        <div style="font-size:2.5rem;margin-bottom:1rem;">🏠</div>
        <div style="font-size:1rem;font-weight:600;color:var(--text);margin-bottom:.5rem;">Tu inmueble está siendo configurado</div>
        <div style="color:var(--text-muted);font-size:.88rem;">Tu asesor lo tendrá listo pronto. Vuelve en un momento.</div>
    </div>
</div>
@else

{{-- ── Stats strip ── --}}
<div class="mi-inmueble-stats">
    <div class="mi-stat-card">
        <div class="mi-stat-val">{{ $totalVisits }}</div>
        <div class="mi-stat-lbl">Visitas agendadas</div>
    </div>
    <div class="mi-stat-card">
        <div class="mi-stat-val">{{ $confirmedVisits }}</div>
        <div class="mi-stat-lbl">Confirmadas</div>
    </div>
    <div class="mi-stat-card">
        <div class="mi-stat-val">{{ $confirmRate }}<span style="font-size:1rem;font-weight:600;">%</span></div>
        <div class="mi-stat-lbl">Tasa de asistencia</div>
    </div>
    @if($daysOnMarket !== null)
    <div class="mi-stat-card">
        <div class="mi-stat-val">{{ $daysOnMarket }}</div>
        <div class="mi-stat-lbl">Días en proceso</div>
    </div>
    @endif
</div>

{{-- ── Pulso de interés ── --}}
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header" style="cursor:pointer;" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'">
        <span style="font-size:.82rem;font-weight:700;">Pulso de interés — últimas 4 semanas</span>
        <span style="font-size:.78rem;color:var(--text-muted);">▼</span>
    </div>
    <div class="card-body">
        @php $maxCount = $weeklyData->max('count') ?: 1; @endphp
        <div class="pulse-bars">
            @foreach($weeklyData as $week)
            @php $pct = round(($week['count'] / $maxCount) * 100); @endphp
            <div class="pulse-bar-wrap">
                <div class="pulse-bar-count">{{ $week['count'] }}</div>
                <div class="pulse-bar" style="height:{{ max(4, $pct) }}%;"></div>
                <div class="pulse-bar-label">{{ $week['label'] }}</div>
            </div>
            @endforeach
        </div>
        @php $thisMonthVisits = $visits->filter(fn($v) => $v->scheduled_at >= now()->startOfMonth())->count(); @endphp
        <div style="font-size:.78rem;color:var(--text-muted);margin-top:.5rem;">
            <strong style="color:#0E304B;">{{ $thisMonthVisits }}</strong> {{ $thisMonthVisits === 1 ? 'visita' : 'visitas' }} este mes
        </div>
    </div>
</div>

{{-- ── Retroalimentación anónima ── --}}
@if($reactionSummary['total'] > 0)
<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header">
        <span style="font-size:.82rem;font-weight:700;">Retroalimentación anónima</span>
        <span style="font-size:.75rem;color:var(--text-muted);">{{ $reactionSummary['total'] }} {{ $reactionSummary['total'] === 1 ? 'opinión' : 'opiniones' }}</span>
    </div>
    <div class="card-body">
        <div class="reaction-row">
            <span>👍 {{ $reactionSummary['liked'] }} {{ $reactionSummary['liked'] === 1 ? 'visitante le gustó' : 'visitantes les gustó' }}</span>
            <span style="font-weight:700;color:#166534;">{{ $reactionSummary['total'] > 0 ? round($reactionSummary['liked'] / $reactionSummary['total'] * 100) : 0 }}%</span>
        </div>
        <div class="prog-bar-bg">
            <div class="prog-bar-fill" style="width:{{ $reactionSummary['total'] > 0 ? round($reactionSummary['liked'] / $reactionSummary['total'] * 100) : 0 }}%;"></div>
        </div>

        <div class="reaction-row" style="margin-top:.75rem;">
            <span>🤔 {{ $reactionSummary['neutral'] }} {{ $reactionSummary['neutral'] === 1 ? 'tiene dudas' : 'tienen dudas' }}</span>
            <span style="font-weight:700;color:#92400E;">{{ $reactionSummary['total'] > 0 ? round($reactionSummary['neutral'] / $reactionSummary['total'] * 100) : 0 }}%</span>
        </div>
        <div class="reaction-row">
            <span>❌ {{ $reactionSummary['disliked'] }} no {{ $reactionSummary['disliked'] === 1 ? 'cumplió expectativas' : 'cumplieron expectativas' }}</span>
            <span style="font-weight:700;color:#991B1B;">{{ $reactionSummary['total'] > 0 ? round($reactionSummary['disliked'] / $reactionSummary['total'] * 100) : 0 }}%</span>
        </div>

        @if($comments->count() > 0)
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
            <div style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Comentarios</div>
            @foreach($comments as $c)
            <div class="comment-card">
                @if($c['reaction'])
                <span style="margin-right:.4rem;">{{ match($c['reaction']) { 'liked' => '👍', 'neutral' => '🤔', 'disliked' => '❌', default => '' } }}</span>
                @endif
                {{ $c['comment'] }}
                <div class="comment-meta">
                    {{ $c['date'] ? $c['date']->locale('es')->diffForHumans() : '' }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif

{{-- ── Historial de visitas ── --}}
<div class="card">
    <div class="card-header">
        <span style="font-size:.82rem;font-weight:700;">Historial de visitas</span>
        @if($totalVisits > 0)
        <span class="badge badge-blue">{{ $totalVisits }}</span>
        @endif
    </div>
    <div class="card-body" style="{{ $visits->isEmpty() ? '' : 'padding-top:.25rem;padding-bottom:.25rem;' }}">
        @forelse($visits as $visit)
        @php
            if ($visit->confirmed_at) {
                $statusLabel = 'Confirmada';
                $statusColor = '#166534';
                $statusBg    = '#dcfce7';
                $dotColor    = '#22C55E';
            } elseif ($visit->reschedule_requested_at) {
                $statusLabel = 'Reagendó';
                $statusColor = '#92400e';
                $statusBg    = '#fef3c7';
                $dotColor    = '#F59E0B';
            } else {
                $statusLabel = 'Pendiente';
                $statusColor = '#475569';
                $statusBg    = '#f1f5f9';
                $dotColor    = '#94A3B8';
            }
        @endphp
        <div class="visit-item">
            <div class="visit-dot" style="background:{{ $dotColor }};"></div>
            <div class="visit-info">
                <div class="visit-date">
                    {{ ucfirst($visit->scheduled_at->locale('es')->isoFormat('dddd D [de] MMMM, YYYY')) }}
                    — {{ $visit->scheduled_at->format('g:i A') }}
                </div>
                <div class="visit-meta">
                    Visitante interesado
                    @if($visit->reschedule_message)
                        &middot; Mensaje: "{{ Str::limit($visit->reschedule_message, 80) }}"
                    @endif
                    @if($visit->visitor_reaction)
                        &middot; {{ match($visit->visitor_reaction) { 'liked' => '👍 Le gustó', 'neutral' => '🤔 Tiene dudas', 'disliked' => '❌ No cumplió', default => '' } }}
                    @endif
                </div>
            </div>
            <span class="badge" style="background:{{ $statusBg }};color:{{ $statusColor }};flex-shrink:0;">{{ $statusLabel }}</span>
        </div>
        @empty
        <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.88rem;">
            No hay visitas registradas aún. Aparecerán aquí conforme lleguen interesados.
        </div>
        @endforelse
    </div>
</div>

@endif {{-- /property exists --}}

@endsection
