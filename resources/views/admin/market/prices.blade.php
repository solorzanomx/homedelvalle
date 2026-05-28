@extends('layouts.app-sidebar')
@section('title', 'Observatorio de Precios')

@section('styles')
/* ── Grid de zonas ───────────────────────────────────────────── */
.zone-cards-grid      { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
.zone-card            { background: var(--card); border: 1px solid var(--border); border-radius: 12px;
                         padding: 1.1rem 1.2rem; display: flex; flex-direction: column; gap: 0; }
.zone-card-header     { display: flex; align-items: flex-start; justify-content: space-between;
                         gap: .5rem; margin-bottom: .75rem; }
.zone-card-title      { font-size: .93rem; font-weight: 700; color: var(--text); margin-bottom: 2px; }
.zone-card-colonias   { font-size: .68rem; color: var(--text-muted); line-height: 1.5; }

/* ── Secciones de precio ─────────────────────────────────────── */
.zone-prices-section  { padding: .6rem 0; border-top: 1px solid var(--border); }
.zone-rent-section    { border-top: 1px dashed #c4b5fd; }
.zone-section-header  { display: flex; align-items: center; justify-content: space-between;
                         margin-bottom: .4rem; }
.zone-section-label   { font-size: .67rem; font-weight: 700; letter-spacing: 1px;
                         text-transform: uppercase; color: var(--text-muted); }
.zone-period-tag      { display: inline-flex; align-items: center; gap: 4px; font-size: .67rem;
                         color: var(--text-muted); }
.conf-dot             { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.prop-type-label      { font-size: .67rem; font-weight: 600; letter-spacing: .4px;
                         text-transform: uppercase; color: var(--text-muted); margin-bottom: 3px; }

/* ── Filas de precio por edad ────────────────────────────────── */
.price-age-rows       { display: flex; flex-direction: column; gap: 2px; margin-bottom: 4px; }
.price-age-row        { display: flex; align-items: center; justify-content: space-between;
                         padding: 3px 0; border-bottom: 1px solid var(--border); font-size: .78rem; }
.price-age-row:last-child { border-bottom: none; }
.age-label            { font-size: .72rem; font-weight: 600; color: var(--text-muted); min-width: 72px; }
.age-label.high       { color: #065f46; }
.age-label.medium     { color: #92400e; }
.age-label.low        { color: #94a3b8; font-style: italic; }
.price-range          { font-size: .74rem; color: var(--text-muted); display: flex; gap: 4px; align-items: center; }
.price-avg            { font-size: .82rem; font-weight: 700; color: var(--text); }
.zone-no-data         { font-size: .72rem; color: var(--text-muted); font-style: italic;
                         padding: .5rem 0; border-top: 1px dashed var(--border); margin-top: .2rem; }

/* ── Botones de zona ─────────────────────────────────────────── */
.zone-card-actions    { display: flex; gap: .3rem; margin-top: .75rem; padding-top: .6rem;
                         border-top: 1px solid var(--border); }
.btn-xs               { font-size: .72rem; padding: 3px 10px; }

/* ── Job status badges ───────────────────────────────────────── */
.run-badges           { display: flex; flex-wrap: wrap; gap: 4px; }
.run-badge            { display: inline-flex; align-items: center; gap: 4px; font-size: .67rem;
                         font-weight: 600; padding: 2px 8px; border-radius: 20px;
                         border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
.run-badge.pending,
.run-badge.running    { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
.run-badge.failed     { border-color: #fecaca; background: #fef2f2; color: #dc2626; }
.run-spinner          { display: inline-block; width: 10px; height: 10px; border: 2px solid #bfdbfe;
                         border-top-color: #2563eb; border-radius: 50%;
                         animation: spin .75s linear infinite; flex-shrink: 0; }
.run-spinner-sm       { display: inline-block; width: 9px; height: 9px; border: 1.5px solid rgba(0,0,0,.15);
                         border-top-color: #1d4ed8; border-radius: 50%;
                         animation: spin .75s linear infinite; vertical-align: middle; }
.run-error-msg        { font-size: .65rem; color: #dc2626; margin-top: 4px;
                         background: #fef2f2; border-radius: 4px; padding: 3px 6px; line-height: 1.4; }

/* ── Progress banner ─────────────────────────────────────────── */
.progress-banner      { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius);
                         padding: .75rem 1rem; display: flex; align-items: center;
                         justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.progress-banner.all-done   { background: #f0fdf4; border-color: #bbf7d0; }
.progress-banner.has-errors { background: #fffbeb; border-color: #fde68a; }
.progress-banner-left { display: flex; align-items: center; gap: .65rem; }
.progress-spinner     { width: 18px; height: 18px; border: 2.5px solid #bfdbfe;
                         border-top-color: #2563eb; border-radius: 50%;
                         animation: spin .75s linear infinite; flex-shrink: 0; }
.progress-title       { font-size: .85rem; font-weight: 700; color: #1e40af; }
.progress-sub         { font-size: .75rem; color: #3b82f6; margin-top: 1px; }
.progress-bar-wrap    { display: flex; align-items: center; gap: 8px; min-width: 160px; }
.progress-bar-track   { flex: 1; height: 8px; background: #dbeafe; border-radius: 4px; overflow: hidden; }
.progress-bar-fill    { height: 100%; background: #2563eb; border-radius: 4px; transition: width .4s ease; }
.progress-bar-fill.has-errors { background: #d97706; }
.progress-pct         { font-size: .75rem; font-weight: 700; color: #1d4ed8; white-space: nowrap; }
@keyframes spin       { to { transform: rotate(360deg); } }
.btn-loading          { opacity: .65; cursor: not-allowed; }
.btn-spinner          { display: inline-block; width: 11px; height: 11px; border: 2px solid rgba(255,255,255,.3);
                         border-top-color: currentColor; border-radius: 50%;
                         animation: spin .75s linear infinite; vertical-align: middle; }
@endsection

@section('content')
<livewire:admin.market-prices-monitor />
@endsection
