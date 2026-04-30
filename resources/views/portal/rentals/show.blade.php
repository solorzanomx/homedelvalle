@extends('layouts.portal')
@section('title', $rental->property?->address ?? 'Mi Renta')

@section('styles')
:root {
    --hdv-navy:  #0C1A2E;
    --hdv-blue:  #1D4ED8;
    --hdv-blue50:#EFF6FF;
}
.rental-hero {
    background: var(--hdv-navy); border-radius: 14px;
    padding: 1.75rem 2rem; margin-bottom: 1.5rem;
    position: relative; overflow: hidden;
}
.rental-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: {{ $role === 'propietario' ? '#10b981' : '#8b5cf6' }};
}
.rental-hero-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    padding: 4px 12px; border-radius: 20px;
    background: {{ $role === 'propietario' ? 'rgba(16,185,129,.2)' : 'rgba(139,92,246,.2)' }};
    color: {{ $role === 'propietario' ? '#6ee7b7' : '#c4b5fd' }};
    border: 1px solid {{ $role === 'propietario' ? 'rgba(16,185,129,.35)' : 'rgba(139,92,246,.35)' }};
    margin-bottom: .85rem;
}
.rental-hero-address { font-size: 1.35rem; font-weight: 700; color: #fff; line-height: 1.25; margin-bottom: .3rem; }
.rental-hero-colony { font-size: .83rem; color: #94A3B8; margin-bottom: 1rem; }
.rental-hero-stats { display: flex; gap: 1.5rem; flex-wrap: wrap; }
.hero-stat-val { font-size: 1.25rem; font-weight: 700; color: #fff; }
.hero-stat-lbl { font-size: .65rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #64748b; }

.stage-pipeline { display: flex; align-items: flex-start; position: relative; overflow-x: auto; padding-bottom: .5rem; }
.stage-pipeline::before { content: ''; position: absolute; top: 16px; left: 16px; right: 16px; height: 2px; background: var(--border); z-index: 0; }
.stage-pip-progress { position: absolute; top: 16px; left: 16px; height: 2px; background: var(--hdv-blue); z-index: 0; transition: width .4s; }
.stage-pip-step { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; min-width: 60px; }
.stage-pip-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; border: 2px solid var(--border); background: var(--card); color: var(--text-muted); margin-bottom: .4rem; }
.stage-pip-dot.done   { background: #ECFDF5; border-color: #10B981; color: #10B981; }
.stage-pip-dot.active { background: var(--hdv-blue); border-color: var(--hdv-blue); color: #fff; box-shadow: 0 0 0 4px rgba(29,78,216,.15); }
.stage-pip-lbl { font-size: .6rem; font-weight: 600; color: var(--text-muted); text-align: center; line-height: 1.3; max-width: 58px; }
.stage-pip-lbl.done   { color: #10B981; }
.stage-pip-lbl.active { color: var(--hdv-blue); }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
@media (max-width: 640px) { .info-grid { grid-template-columns: 1fr; } }
.info-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.info-card-hd { padding: .65rem 1.1rem; border-bottom: 1px solid var(--border); font-size: .75rem; font-weight: 700; color: var(--text); }
.info-row { display: flex; justify-content: space-between; align-items: flex-start; padding: .6rem 1.1rem; border-bottom: 1px solid var(--border); gap: 1rem; }
.info-row:last-child { border-bottom: none; }
.info-lbl { font-size: .72rem; color: var(--text-muted); flex-shrink: 0; min-width: 90px; }
.info-val { font-size: .82rem; font-weight: 500; color: var(--text); text-align: right; }

.section-wrap { background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 1.25rem; }
.section-hd { padding: .75rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.section-hd-title { font-size: .82rem; font-weight: 700; color: var(--text); }
.section-body { padding: 1.25rem; }

.payment-row { display: flex; align-items: center; gap: .85rem; padding: .6rem 1.25rem; border-bottom: 1px solid var(--border); font-size: .82rem; }
.payment-row:last-child { border-bottom: none; }
.payment-month { width: 68px; flex-shrink: 0; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--text-muted); }
.payment-amount { flex: 1; font-weight: 600; color: var(--text); }
.payment-badge { font-size: .65rem; font-weight: 700; padding: .2rem .55rem; border-radius: 9999px; }
@endsection

@section('content')
@php
    $stageKeys  = array_keys(\App\Models\RentalProcess::STAGES);
    $currentIdx = array_search($rental->stage, $stageKeys) ?? 0;
    $totalSteps = count($stageKeys);
    $lineWidthPct = $totalSteps > 1 ? round(($currentIdx / ($totalSteps - 1)) * 100) . '%' : '0%';

    $hasPaymentDay = \Illuminate\Support\Facades\Schema::hasColumn('rental_processes', 'payment_day');

    // Timeline de pagos
    $paymentTimeline = [];
    if ($rental->lease_start_date && $rental->lease_end_date && $rental->monthly_rent) {
        $start = $rental->lease_start_date->copy();
        $end   = $rental->lease_end_date->copy();
        $today = now();
        $d = $start->copy()->startOfMonth();
        while ($d->lte($end) && count($paymentTimeline) < 36) {
            if ($d->gte($start->copy()->startOfMonth())) {
                $dueDate = $d->copy();
                if ($hasPaymentDay && $rental->payment_day) {
                    try { $dueDate = $d->copy()->setDay($rental->payment_day); } catch (\Throwable $e) {}
                }
                $isPast    = $dueDate->lt($today->copy()->startOfMonth());
                $isCurrent = $dueDate->format('Y-m') === $today->format('Y-m');
                $paymentTimeline[] = [
                    'label'     => ucfirst($dueDate->locale('es')->isoFormat('MMM YYYY')),
                    'due'       => $dueDate->format('d/m/Y'),
                    'isPast'    => $isPast,
                    'isCurrent' => $isCurrent,
                ];
            }
            $d->addMonth();
        }
    }
@endphp

{{-- Back --}}
<a href="{{ route('portal.rentals.index') }}"
   style="display:inline-flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--text-muted);text-decoration:none;margin-bottom:1.25rem;">
    ← Mis rentas
</a>

{{-- Hero --}}
<div class="rental-hero">
    <div class="rental-hero-badge">{{ $role === 'propietario' ? '🏠 Propietario' : '🔑 Inquilino' }}</div>
    <div class="rental-hero-address">{{ $rental->property?->address ?? 'Renta #' . $rental->id }}</div>
    @if($rental->property?->colony)
    <div class="rental-hero-colony">{{ $rental->property->colony }}@if($rental->property?->city) &nbsp;·&nbsp; {{ $rental->property->city }}@endif</div>
    @endif
    <div class="rental-hero-stats">
        @if($rental->monthly_rent)
        <div><div class="hero-stat-val">${{ number_format($rental->monthly_rent, 0) }}</div><div class="hero-stat-lbl">Renta/mes</div></div>
        @endif
        @if($rental->lease_start_date)
        <div><div class="hero-stat-val">{{ $rental->lease_start_date->format('d/m/Y') }}</div><div class="hero-stat-lbl">Inicio</div></div>
        @endif
        @if($rental->lease_end_date)
        @php $daysLeft = now()->diffInDays($rental->lease_end_date, false); @endphp
        <div>
            <div class="hero-stat-val" style="{{ $daysLeft < 0 ? 'color:#f87171;' : ($daysLeft <= 60 ? 'color:#fbbf24;' : '') }}">
                {{ $rental->lease_end_date->format('d/m/Y') }}
            </div>
            <div class="hero-stat-lbl">{{ $daysLeft < 0 ? 'Vencido' : ($daysLeft <= 60 ? $daysLeft.' días' : 'Vencimiento') }}</div>
        </div>
        @endif
        @if($rental->lease_duration_months)
        <div><div class="hero-stat-val">{{ $rental->lease_duration_months }}m</div><div class="hero-stat-lbl">Duración</div></div>
        @endif
    </div>
</div>

{{-- Pipeline --}}
<div class="section-wrap">
    <div class="section-hd">
        <span class="section-hd-title">Estado del proceso</span>
        <span style="font-size:.72rem;font-weight:600;padding:.2rem .65rem;border-radius:9999px;background:{{ $rental->stage_color ?? '#e2e8f0' }}20;color:{{ $rental->stage_color ?? '#64748b' }};">
            {{ $rental->stage_label }}
        </span>
    </div>
    <div style="padding:1.25rem 1.5rem;">
        <div class="stage-pipeline">
            <div class="stage-pip-progress" style="width:{{ $lineWidthPct }};"></div>
            @foreach($stageKeys as $i => $sk)
            @php $sc = $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'active' : ''); @endphp
            <div class="stage-pip-step">
                <div class="stage-pip-dot {{ $sc }}">{{ $i < $currentIdx ? '✓' : ($i + 1) }}</div>
                <div class="stage-pip-lbl {{ $sc }}">{{ \App\Models\RentalProcess::STAGES[$sk] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Info grid --}}
<div class="info-grid">
    <div class="info-card">
        <div class="info-card-hd">🏠 Propiedad</div>
        @if($rental->property)
            @if($rental->property->address)<div class="info-row"><span class="info-lbl">Dirección</span><span class="info-val">{{ $rental->property->address }}</span></div>@endif
            @if($rental->property->colony)<div class="info-row"><span class="info-lbl">Colonia</span><span class="info-val">{{ $rental->property->colony }}</span></div>@endif
            @if($rental->property->bedrooms)<div class="info-row"><span class="info-lbl">Recámaras</span><span class="info-val">{{ $rental->property->bedrooms }}</span></div>@endif
            @if($rental->property->bathrooms)<div class="info-row"><span class="info-lbl">Baños</span><span class="info-val">{{ $rental->property->bathrooms }}</span></div>@endif
        @else
            <div class="info-row"><span class="info-lbl" style="color:#94a3b8;">Sin información registrada.</span></div>
        @endif
    </div>
    <div class="info-card">
        <div class="info-card-hd">📄 Contrato</div>
        @if($rental->monthly_rent)<div class="info-row"><span class="info-lbl">Renta</span><span class="info-val" style="color:var(--hdv-blue);font-weight:700;">${{ number_format($rental->monthly_rent, 0) }} {{ $rental->currency ?? 'MXN' }}/mes</span></div>@endif
        @if($rental->deposit_amount)<div class="info-row"><span class="info-lbl">Depósito</span><span class="info-val">${{ number_format($rental->deposit_amount, 0) }}</span></div>@endif
        @if($rental->guarantee_type)<div class="info-row"><span class="info-lbl">Garantía</span><span class="info-val">{{ $rental->guarantee_type_label }}</span></div>@endif
        @if($rental->lease_start_date)<div class="info-row"><span class="info-lbl">Inicio</span><span class="info-val">{{ $rental->lease_start_date->format('d/m/Y') }}</span></div>@endif
        @if($rental->lease_end_date)
        <div class="info-row">
            <span class="info-lbl">Vencimiento</span>
            <span class="info-val">{{ $rental->lease_end_date->format('d/m/Y') }}
            @php $dl = now()->diffInDays($rental->lease_end_date, false); @endphp
            @if($dl < 0)<span style="font-size:.62rem;font-weight:700;padding:.1rem .4rem;border-radius:9999px;background:#fef2f2;color:#ef4444;margin-left:.3rem;">Vencido</span>@elseif($dl <= 60)<span style="font-size:.62rem;font-weight:700;padding:.1rem .4rem;border-radius:9999px;background:#fffbeb;color:#f59e0b;margin-left:.3rem;">{{ $dl }}d</span>@endif
            </span>
        </div>
        @endif
        @if($rental->broker)<div class="info-row"><span class="info-lbl">Asesor</span><span class="info-val">{{ $rental->broker->name }}</span></div>@endif
    </div>
</div>

{{-- Timeline de pagos --}}
@if(count($paymentTimeline) > 0)
<div class="section-wrap">
    <div class="section-hd">
        <span class="section-hd-title">📅 Calendario de pagos</span>
        <span style="font-size:.72rem;color:var(--text-muted);">{{ $rental->lease_duration_months }} meses</span>
    </div>
    <div style="max-height:300px;overflow-y:auto;">
        @foreach($paymentTimeline as $pmt)
        @php
            $bg = $pmt['isCurrent'] ? '#eff6ff' : ($pmt['isPast'] ? '#f8fafc' : '#fff');
            $bl = $pmt['isCurrent'] ? 'border-left:3px solid #1D4ED8;' : '';
        @endphp
        <div class="payment-row" style="background:{{ $bg }};{{ $bl }}">
            <div class="payment-month">{{ $pmt['label'] }}</div>
            <div class="payment-amount">
                ${{ number_format($rental->monthly_rent, 0) }}
                @if($pmt['isCurrent'])<span style="font-size:.65rem;color:#1D4ED8;font-weight:600;margin-left:.3rem;">Este mes</span>@endif
            </div>
            @if($pmt['isPast'])
            <span class="payment-badge" style="background:#f0fdf4;color:#10b981;">✓ Pagado</span>
            @elseif($pmt['isCurrent'])
            <span class="payment-badge" style="background:#eff6ff;color:#1D4ED8;">● Vigente</span>
            @else
            <span class="payment-badge" style="background:#f8fafc;color:#94a3b8;">Próximo</span>
            @endif
            <div style="font-size:.7rem;color:var(--text-muted);">{{ $pmt['due'] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Documentos Livewire --}}
<div class="section-wrap">
    <div class="section-hd"><span class="section-hd-title">📎 Documentos</span></div>
    <div class="section-body">
        @livewire('portal.document-uploader', [
            'rentalProcessId'   => $rental->id,
            'allowedCategories' => ['identificacion','comprobante_domicilio','proof_of_income','credit_report','references','rental_contract','other'],
        ])
    </div>
</div>

{{-- Contratos --}}
@if($rental->contracts->isNotEmpty())
<div class="section-wrap">
    <div class="section-hd"><span class="section-hd-title">🤝 Contratos ({{ $rental->contracts->count() }})</span></div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
            <thead><tr style="background:#f8fafc;">
                <th style="padding:.55rem 1.1rem;text-align:left;font-size:.65rem;font-weight:700;text-transform:uppercase;color:#64748b;">Contrato</th>
                <th style="padding:.55rem 1.1rem;text-align:left;font-size:.65rem;font-weight:700;text-transform:uppercase;color:#64748b;">Tipo</th>
                <th style="padding:.55rem 1.1rem;text-align:center;font-size:.65rem;font-weight:700;text-transform:uppercase;color:#64748b;">Firma</th>
                <th style="padding:.55rem 1.1rem;text-align:center;font-size:.65rem;font-weight:700;text-transform:uppercase;color:#64748b;">Fecha</th>
                <th style="padding:.55rem 1.1rem;"></th>
            </tr></thead>
            <tbody>
                @foreach($rental->contracts->sortByDesc('created_at') as $contract)
                @php $sc = match($contract->signature_status) { 'signed' => '#10b981', 'pending_signature' => '#f59e0b', default => '#3b82f6' }; @endphp
                <tr style="border-top:1px solid #e2e8f0;">
                    <td style="padding:.6rem 1.1rem;font-weight:600;color:#0f172a;">{{ $contract->title }}</td>
                    <td style="padding:.6rem 1.1rem;color:#64748b;">{{ \App\Models\ContractTemplate::TYPES[$contract->type] ?? ucfirst($contract->type) }}</td>
                    <td style="padding:.6rem 1.1rem;text-align:center;"><span style="font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ $sc }}20;color:{{ $sc }};">{{ $contract->signature_status_label }}</span></td>
                    <td style="padding:.6rem 1.1rem;text-align:center;color:#64748b;font-size:.75rem;">{{ $contract->created_at->format('d/m/Y') }}</td>
                    <td style="padding:.6rem 1.1rem;text-align:right;">@if($contract->pdf_path)<a href="{{ route('contracts.download', $contract->id) }}" style="font-size:.72rem;font-weight:600;color:#1D4ED8;">↓ PDF</a>@endif</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Historial --}}
@if($rental->stageLogs->isNotEmpty())
<div class="section-wrap">
    <div class="section-hd"><span class="section-hd-title">🕐 Historial del proceso</span></div>
    <div style="padding:0 1.25rem;">
        @foreach($rental->stageLogs->sortByDesc('created_at') as $log)
        <div style="display:flex;gap:.75rem;padding:.65rem 0;border-bottom:1px solid #f1f5f9;">
            <div style="width:30px;height:30px;border-radius:50%;background:#eff6ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#1D4ED8;flex-shrink:0;margin-top:.1rem;">→</div>
            <div style="flex:1;">
                <p style="font-size:.82rem;color:#0f172a;">
                    <span style="color:#64748b;">{{ \App\Models\RentalProcess::STAGES[$log->from_stage] ?? $log->from_stage }}</span>
                    &nbsp;→&nbsp;<strong>{{ \App\Models\RentalProcess::STAGES[$log->to_stage] ?? $log->to_stage }}</strong>
                </p>
                @if($log->notes)<p style="font-size:.72rem;color:#64748b;margin-top:.1rem;">{{ $log->notes }}</p>@endif
                <p style="font-size:.68rem;color:#94a3b8;margin-top:.15rem;">{{ $log->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
