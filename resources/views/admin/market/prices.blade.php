@extends('layouts.app-sidebar')
@section('title', 'Precios de Mercado')

@section('styles')
/* ── Layout ───────────────────────────────────────────────────── */
.zone-section        { margin-bottom: 2rem; }
.zone-header         { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.zone-title          { font-size: 0.95rem; font-weight: 700; color: var(--text); }
.zone-pill           { font-size: 0.7rem; background: var(--bg); border: 1px solid var(--border);
                        border-radius: 20px; padding: 2px 10px; color: var(--text-muted); }
.zone-pill.active    { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
.colonia-grid        { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 0.75rem; }

/* ── Cards ───────────────────────────────────────────────────── */
.colonia-card        { background: var(--card); border: 1px solid var(--border); border-radius: 10px;
                        padding: 1rem 1.1rem; transition: opacity .2s; }
.colonia-card.inactive { opacity: .55; }

.card-top            { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem; }
.colonia-name        { font-size: 0.88rem; font-weight: 600; color: var(--text); }
.colonia-cp          { font-size: 0.72rem; color: var(--text-muted); }

/* ── Toggle switch ───────────────────────────────────────────── */
.toggle-form         { display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; }
.toggle-label        { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; }
.toggle-wrap         { position: relative; width: 36px; height: 20px; }
.toggle-wrap input   { opacity: 0; width: 0; height: 0; }
.toggle-slider       { position: absolute; inset: 0; background: #d1d5db; border-radius: 20px;
                        cursor: pointer; transition: background .2s; }
.toggle-slider::after { content: ''; position: absolute; left: 3px; top: 3px;
                         width: 14px; height: 14px; border-radius: 50%;
                         background: #fff; transition: transform .2s; }
.toggle-wrap input:checked + .toggle-slider             { background: #1d4ed8; }
.toggle-wrap input:checked + .toggle-slider::after      { transform: translateX(16px); }

/* ── Price data ──────────────────────────────────────────────── */
.price-section       { margin-top: 0.5rem; }
.price-type-label    { font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
                        letter-spacing: .5px; color: var(--text-muted); margin-bottom: 3px; }
.price-row           { display: flex; align-items: center; justify-content: space-between;
                        padding: 2px 0; border-bottom: 1px solid var(--border); font-size: 0.76rem; }
.price-row:last-child { border-bottom: none; }
.price-cat           { color: var(--text-muted); }
.price-val           { font-weight: 600; color: var(--text); }
.no-data             { font-size: 0.75rem; color: var(--text-muted); font-style: italic; padding: 4px 0; }
.period-tag          { display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem;
                        background: var(--bg); border: 1px solid var(--border); border-radius: 20px;
                        padding: 1px 8px; color: var(--text-muted); margin-bottom: 0.4rem; }
.conf-dot            { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── Actions bar ─────────────────────────────────────────────── */
.card-actions        { display: flex; gap: 0.4rem; margin-top: 0.6rem; padding-top: 0.6rem;
                        border-top: 1px solid var(--border); }
.btn-xs              { font-size: 0.72rem; padding: 3px 10px; }

/* ── Job status badges per card ──────────────────────────────── */
.run-badges          { display: flex; flex-wrap: wrap; gap: 4px; margin: 6px 0; }
.run-badge           { display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem;
                        font-weight: 600; padding: 2px 8px; border-radius: 20px;
                        border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
.run-badge.pending,
.run-badge.running   { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
.run-badge.failed    { border-color: #fecaca; background: #fef2f2; color: #dc2626; }

.run-spinner         { display: inline-block; width: 10px; height: 10px; border: 2px solid #bfdbfe;
                        border-top-color: #2563eb; border-radius: 50%;
                        animation: spin .75s linear infinite; flex-shrink: 0; }
.run-spinner-sm      { display: inline-block; width: 9px; height: 9px; border: 1.5px solid rgba(0,0,0,.15);
                        border-top-color: #1d4ed8; border-radius: 50%;
                        animation: spin .75s linear infinite; vertical-align: middle; }
.run-time            { font-size: 0.6rem; color: var(--text-muted); font-weight: 400; }
.run-error-msg       { font-size: 0.65rem; color: #dc2626; margin-top: 4px;
                        background: #fef2f2; border-radius: 4px; padding: 3px 6px; line-height: 1.4; }

/* ── Progress banner ─────────────────────────────────────────── */
.progress-banner     { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius);
                        padding: 0.75rem 1rem; display: flex; align-items: center;
                        justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.progress-banner.all-done   { background: #f0fdf4; border-color: #bbf7d0; }
.progress-banner.has-errors { background: #fffbeb; border-color: #fde68a; }

.progress-banner-left { display: flex; align-items: center; gap: 0.65rem; }
.progress-spinner    { width: 18px; height: 18px; border: 2.5px solid #bfdbfe;
                        border-top-color: #2563eb; border-radius: 50%;
                        animation: spin .75s linear infinite; flex-shrink: 0; }
.progress-title      { font-size: 0.85rem; font-weight: 700; color: #1e40af; }
.progress-sub        { font-size: 0.75rem; color: #3b82f6; margin-top: 1px; }

.progress-bar-wrap   { display: flex; align-items: center; gap: 8px; min-width: 160px; }
.progress-bar-track  { flex: 1; height: 8px; background: #dbeafe; border-radius: 4px; overflow: hidden; }
.progress-bar-fill   { height: 100%; background: #2563eb; border-radius: 4px;
                        transition: width .4s ease; }
.progress-bar-fill.has-errors { background: #d97706; }
.progress-pct        { font-size: 0.75rem; font-weight: 700; color: #1d4ed8; white-space: nowrap; }

/* ── Spinner animation ───────────────────────────────────────── */
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Btn loading state ───────────────────────────────────────── */
.btn-loading         { opacity: .65; cursor: not-allowed; }
.btn-spinner         { display: inline-block; width: 11px; height: 11px; border: 2px solid rgba(255,255,255,.3);
                        border-top-color: currentColor; border-radius: 50%;
                        animation: spin .75s linear infinite; vertical-align: middle; }
@endsection

@section('content')
<livewire:admin.market-prices-monitor />
@endsection
