@extends('layouts.app-sidebar')
@section('title', 'Lead Scoring')

@section('styles')
<style>
.score-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem; }
.score-header h2 { font-size: 1.15rem; font-weight: 700; }

.score-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .score-layout { grid-template-columns: 1fr; } }

.score-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 1rem; overflow: hidden; }
.score-card-header { padding: 0.8rem 1.25rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.88rem; }
.score-card-body { padding: 1.25rem; }

/* Grade cards */
.grade-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
.grade-card { text-align: center; padding: 1rem; border-radius: 10px; border: 1px solid var(--border); background: var(--card); }
.grade-letter { font-size: 2rem; font-weight: 800; }
.grade-count { font-size: 1.3rem; font-weight: 700; margin-top: 0.15rem; }
.grade-label { font-size: 0.72rem; color: var(--text-muted); }
@media (max-width: 640px) { .grade-grid { grid-template-columns: repeat(2, 1fr); } }

/* Leaderboard */
.lead-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0; border-bottom: 1px solid var(--border); }
.lead-row:last-child { border-bottom: none; }
.lead-rank { width: 28px; height: 28px; border-radius: 50%; background: var(--bg); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
.lead-rank.top1 { background: #fef3c7; color: #92400e; }
.lead-rank.top2 { background: #e0e7ff; color: #3730a3; }
.lead-rank.top3 { background: #fce7f3; color: #9d174d; }
.lead-avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.82rem; color: #fff; flex-shrink: 0; }
.lead-info { flex: 1; min-width: 0; }
.lead-name { font-size: 0.88rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lead-sub { font-size: 0.72rem; color: var(--text-muted); }
.lead-score { font-size: 1rem; font-weight: 700; }
.lead-grade { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.82rem; color: #fff; }

/* Rules */
.rule-table { width: 100%; font-size: 0.85rem; }
.rule-table th { text-align: left; padding: 0.5rem 0; color: var(--text-muted); font-size: 0.72rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border); }
.rule-table td { padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
.rule-table tr:last-child td { border-bottom: none; }
.rule-pts { font-weight: 700; color: var(--primary); }
.rule-event { font-weight: 500; }
</style>
@endsection

@section('content')
<div class="score-header">
    <div>
        <h2>&#127942; Lead Scoring</h2>
        <p style="font-size:0.82rem; color:var(--text-muted);">Puntuacion automatica de leads basada en su comportamiento.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

{{-- Grade distribution --}}
<div class="grade-grid">
    @foreach(\App\Models\LeadScore::GRADES as $g => $info)
    <div class="grade-card">
        <div class="grade-letter" style="color:{{ $info['color'] }};">{{ $g }}</div>
        <div class="grade-count">{{ $gradeDistribution[$g] ?? 0 }}</div>
        <div class="grade-label">{{ $info['label'] }}</div>
    </div>
    @endforeach
</div>

<div class="score-layout">
    <div>
        {{-- Leaderboard --}}
        <div class="score-card">
            <div class="score-card-header">Top Leads por Score</div>
            <div class="score-card-body" style="padding:0.75rem 1.25rem;">
                @forelse($scores as $i => $score)
                @php $client = $score->client; @endphp
                <div class="lead-row">
                    <div class="lead-rank {{ $scores->currentPage() === 1 && $i < 3 ? 'top'.($i+1) : '' }}">{{ ($scores->currentPage()-1) * $scores->perPage() + $i + 1 }}</div>
                    <div class="lead-avatar" style="background: {{ \App\Models\LeadScore::GRADES[$score->grade]['color'] ?? '#6b7280' }};">
                        {{ strtoupper(substr($client->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="lead-info">
                        <div class="lead-name">
                            @if($client)<a href="{{ route('clients.show', $client) }}" style="color:inherit; text-decoration:none;">{{ $client->name }}</a>@else Eliminado @endif
                        </div>
                        <div class="lead-sub">
                            E:{{ $score->engagement_score }} &middot; A:{{ $score->activity_score }} &middot; P:{{ $score->profile_score }}
                            @if($score->last_activity_at) &middot; {{ $score->last_activity_at->diffForHumans() }}@endif
                        </div>
                    </div>
                    <div class="lead-score">{{ $score->total_score }}</div>
                    <div class="lead-grade" style="background: {{ \App\Models\LeadScore::GRADES[$score->grade]['color'] ?? '#6b7280' }};">{{ $score->grade }}</div>
                </div>
                @empty
                <p style="text-align:center; color:var(--text-muted); padding:2rem;">Sin leads con score todavia. El scoring se activa automaticamente con las interacciones.</p>
                @endforelse

                @if($scores->hasPages())
                <div style="margin-top:1rem;">{{ $scores->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar: Rules --}}
    <div>
        <div class="score-card">
            <div class="score-card-header">Reglas de Scoring</div>
            <div class="score-card-body">
                <table class="rule-table">
                    <thead><tr><th>Evento</th><th>Puntos</th><th>Max/dia</th></tr></thead>
                    <tbody>
                    @foreach($rules as $rule)
                    <tr>
                        <td>
                            <div class="rule-event">{{ $rule->description }}</div>
                            <div style="font-size:0.68rem; color:var(--text-muted);">{{ $rule->event }}</div>
                        </td>
                        <td class="rule-pts">+{{ $rule->points }}</td>
                        <td style="font-size:0.82rem;">{{ $rule->max_per_day ?: '∞' }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="score-card">
            <div class="score-card-header">Como funciona</div>
            <div class="score-card-body">
                <div style="font-size:0.82rem; line-height:1.6; color:var(--text-muted);">
                    <p>El score se compone de 3 areas:</p>
                    <p><strong style="color:var(--text);">Engagement (E):</strong> aperturas, respuestas, formularios</p>
                    <p><strong style="color:var(--text);">Actividad (A):</strong> llamadas, visitas, tareas</p>
                    <p><strong style="color:var(--text);">Perfil (P):</strong> datos completos del cliente</p>
                    <p style="margin-top:0.5rem;">Los grados se asignan automaticamente: A (80+), B (50+), C (20+), D (menos de 20).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
