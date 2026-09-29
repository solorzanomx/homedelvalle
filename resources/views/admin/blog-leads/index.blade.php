@extends('layouts.app-sidebar')
@section('title', 'Blog → Leads')

@section('content')
<div class="page-header">
    <div>
        <h2>Blog → Leads</h2>
        <p class="text-muted">{{ $totalLeads }} leads atribuidos al blog en los últimos {{ $rangeDays }} días.</p>
    </div>
    <div style="display:flex;gap:.4rem;">
        @foreach([7,30,90] as $r)
        <a href="{{ route('admin.blog-leads.index', ['range' => $r]) }}" class="btn btn-sm {{ $rangeDays === $r ? 'btn-primary' : 'btn-outline' }}">{{ $r }} días</a>
        @endforeach
    </div>
</div>

<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h3>Leads por post</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="data-table">
            <thead><tr><th>Post</th><th>Vistas totales</th><th>Leads ({{ $rangeDays }}d)</th><th>Conversión aprox.</th></tr></thead>
            <tbody>
                @forelse($byPost as $row)
                <tr>
                    <td><a href="{{ route('blog.show', $row['slug']) }}" target="_blank">{{ $row['title'] }}</a></td>
                    <td>{{ number_format($row['views_count']) }}</td>
                    <td>{{ $row['conversions'] }}</td>
                    <td>{{ $row['rate'] !== null ? $row['rate'].'%' : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:2rem;">Sin leads atribuidos al blog en este rango.</td></tr>
                @endforelse
            </tbody>
        </table>
        <p style="font-size:.72rem;color:var(--text-muted);padding:.6rem 1rem;">"Conversión aprox." compara los leads del rango contra las vistas TOTALES históricas del post — no es una tasa periodo a periodo estrictamente comparable, es una referencia rápida de qué tan bien convierte cada artículo.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
    <div class="card">
        <div class="card-header"><h3>Leads por cluster</h3></div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead><tr><th>Cluster</th><th>Leads</th></tr></thead>
                <tbody>
                    @forelse($byCluster as $row)
                    <tr><td>{{ $row['label'] }}</td><td>{{ $row['count'] }}</td></tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:var(--text-muted);padding:1.5rem;">Sin datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3>Leads por variante de CTA</h3></div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead><tr><th>Variante</th><th>Leads</th></tr></thead>
                <tbody>
                    @forelse($byCtaVariant as $row)
                    <tr><td>{{ $row['variant'] }}</td><td>{{ $row['count'] }}</td></tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:var(--text-muted);padding:1.5rem;">Solo los leads de los CTAs por cluster (Fase 3/4) traen variante.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3>Redirects con más tráfico</h3>
            <a href="{{ route('admin.blog-redirects.index') }}" class="btn btn-sm btn-outline">Administrar</a>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead><tr><th>Desde</th><th>Hacia</th><th>Hits</th><th></th></tr></thead>
                <tbody>
                    @forelse($redirectHits as $r)
                    <tr>
                        <td style="font-size:.76rem;">{{ $r->from_path }}</td>
                        <td style="font-size:.76rem;">{{ $r->to_path }}</td>
                        <td>{{ $r->hits }}</td>
                        <td>{{ $r->active ? '' : '<span class="badge badge-yellow">inactivo</span>' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:1.5rem;">Sin redirects todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3>404 más frecuentes</h3></div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead><tr><th>Slug buscado</th><th>Hits</th><th>Último</th></tr></thead>
                <tbody>
                    @forelse($notFoundHits as $h)
                    <tr>
                        <td style="font-size:.76rem;">/blog/{{ $h->slug }}</td>
                        <td>{{ $h->hits }}</td>
                        <td style="font-size:.75rem;color:var(--text-muted);">{{ $h->last_hit_at?->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:1.5rem;">Sin 404 registrados — buena señal.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p style="font-size:.72rem;color:var(--text-muted);padding:.6rem 1rem;">Si uno de estos se repite mucho, créale un redirect manual en <a href="{{ route('admin.blog-redirects.index') }}">Redirects</a>.</p>
        </div>
    </div>
</div>
@endsection
