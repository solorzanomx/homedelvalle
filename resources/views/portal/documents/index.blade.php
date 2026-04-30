@extends('layouts.portal')
@section('title', 'Mis Documentos')

@section('styles')
:root { --hdv-blue: #1D4ED8; }
.doc-stats { display:flex; gap:.65rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.doc-stat  { display:flex; align-items:center; gap:.5rem; padding:.5rem .85rem; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); white-space:nowrap; }
.doc-stat-icon { width:30px; height:30px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:.8rem; flex-shrink:0; }
.doc-stat-val  { font-size:1rem; font-weight:700; color:var(--text); line-height:1; }
.doc-stat-lbl  { font-size:.68rem; color:var(--text-muted); }
@endsection

@section('content')
@php
    $totalDocs = $documents->count() + $captacionDocuments->count();
    $approved  = $documents->where('status','verified')->count() + $captacionDocuments->where('captacion_status','aprobado')->count();
    $inReview  = $documents->whereIn('status',['pending','received'])->count() + $captacionDocuments->where('captacion_status','pendiente')->count();
    $rejected  = $documents->where('status','rejected')->count() + $captacionDocuments->where('captacion_status','rechazado')->count();
@endphp

<div class="page-header">
    <div>
        <h2>Mis Documentos</h2>
        <p style="color:var(--text-muted);font-size:.82rem;">{{ $totalDocs }} documento{{ $totalDocs !== 1 ? 's' : '' }} en total</p>
    </div>
</div>

{{-- Stats --}}
@if($totalDocs > 0)
<div class="doc-stats">
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(29,78,216,.08);color:var(--hdv-blue);">📄</div>
        <div><div class="doc-stat-val">{{ $totalDocs }}</div><div class="doc-stat-lbl">Total</div></div>
    </div>
    @if($approved > 0)
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(16,185,129,.1);color:#10b981;">✓</div>
        <div><div class="doc-stat-val" style="color:#10b981;">{{ $approved }}</div><div class="doc-stat-lbl">Aprobados</div></div>
    </div>
    @endif
    @if($inReview > 0)
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;">●</div>
        <div><div class="doc-stat-val" style="color:#f59e0b;">{{ $inReview }}</div><div class="doc-stat-lbl">En revisión</div></div>
    </div>
    @endif
    @if($rejected > 0)
    <div class="doc-stat">
        <div class="doc-stat-icon" style="background:rgba(239,68,68,.1);color:#ef4444;">✕</div>
        <div><div class="doc-stat-val" style="color:#ef4444;">{{ $rejected }}</div><div class="doc-stat-lbl">Rechazados</div></div>
    </div>
    @endif
</div>
@endif

{{-- Documentos de captación (read-only desde aquí) --}}
@if($captacionDocuments->isNotEmpty())
<div style="background:var(--card);border:1px solid var(--border);border-radius:12px;margin-bottom:1.5rem;overflow:hidden;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.82rem;font-weight:700;">Evaluación de Propiedad</span>
        <a href="{{ route('portal.captacion') }}" style="font-size:.75rem;color:var(--hdv-blue);">Ver expediente →</a>
    </div>
    <div style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.5rem;">
        @foreach($captacionDocuments as $doc)
        @php
            $sc = match($doc->captacion_status) { 'aprobado' => '#10b981', 'rechazado' => '#ef4444', default => '#f59e0b' };
            $sl = match($doc->captacion_status) { 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', default => 'En revisión' };
        @endphp
        <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem .85rem;background:#f8fafc;border:1px solid var(--border);border-radius:9px;">
            <span>📄</span>
            <div style="flex:1;min-width:0;">
                <p style="font-weight:600;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->label ?? $doc->file_name }}</p>
                <p style="font-size:.68rem;color:var(--text-muted);">{{ ($allCategories ?? [])[$doc->category] ?? $doc->category }} &middot; {{ $doc->created_at->format('d/m/Y') }}</p>
                @if($doc->captacion_status === 'rechazado' && $doc->rejection_reason)
                <p style="font-size:.68rem;color:#ef4444;margin-top:.1rem;">⚠ {{ $doc->rejection_reason }}</p>
                @endif
            </div>
            <span style="font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ $sc }}20;color:{{ $sc }};flex-shrink:0;">{{ $sl }}</span>
            <a href="{{ route('portal.documents.download', $doc->id) }}"
               style="font-size:.72rem;font-weight:600;color:#64748b;text-decoration:none;border:1px solid var(--border);border-radius:6px;padding:.3rem .6rem;flex-shrink:0;">↓</a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Documentos generales — Livewire uploader --}}
<div style="background:var(--card);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border);">
        <span style="font-size:.82rem;font-weight:700;">Mis documentos</span>
    </div>
    <div style="padding:1.25rem;">
        @if($client)
        @livewire('portal.document-uploader', ['rentalProcessId' => null, 'allowedCategories' => []])
        @else
        <div style="text-align:center;padding:2.5rem;color:#94a3b8;">
            <div style="font-size:2rem;margin-bottom:.5rem;opacity:.4;">📂</div>
            <p style="font-size:.83rem;">Tu cuenta está siendo configurada. Pronto podrás subir documentos.</p>
        </div>
        @endif
    </div>
</div>

@endsection
