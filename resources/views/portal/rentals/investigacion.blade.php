@extends('layouts.portal')

@section('title', 'Candidato para tu inmueble')

@section('content')
@php
    $decision     = $inv->owner_decision;
    $alreadyDone  = in_array($decision, ['approved', 'declined']);
    $addr         = $rental->property?->address ?? '—';
    $colony       = $rental->property?->colony ? ', ' . $rental->property->colony : '';

    // Semáforo relación renta/ingreso
    $ratio        = $inv->rent_income_ratio;
    $ratioColor   = $inv->rent_income_ratio_color;
    $ratioLabel   = $inv->rent_income_ratio_label;

    // Colores recomendación
    $recColors = ['approve' => '#10b981', 'conditional' => '#f59e0b', 'decline' => '#ef4444'];
    $recBgs    = ['approve' => '#dcfce7', 'conditional' => '#fef3c7', 'decline' => '#fee2e2'];
    $recColor  = $recColors[$inv->asesor_recommendation] ?? '#94a3b8';
    $recBg     = $recBgs[$inv->asesor_recommendation] ?? '#f1f5f9';

    // Estado decisión
    $decisionConfigs = [
        'approved'  => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => '✓ Aprobaste este candidato'],
        'declined'  => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => '✕ Declinaste este candidato'],
        'more_info' => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => '→ Solicitaste más información'],
    ];
    $decisionCfg = $decisionConfigs[$decision] ?? null;
@endphp

{{-- Page header --}}
<div class="page-header" style="margin-bottom:1.5rem;">
    <div>
        <a href="{{ route('portal.rentals.show', $rental->id) }}"
           style="font-size:.8rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.5rem;">
            &#8592; Volver al proceso
        </a>
        <h2 style="font-size:1.3rem;font-weight:800;color:var(--text);margin:0;">Candidato propuesto</h2>
        <p style="font-size:.85rem;color:var(--text-muted);margin:.3rem 0 0;">
            {{ $addr }}{{ $colony }}
        </p>
    </div>
    <span style="background:#ede9fe;color:#5b21b6;padding:.35rem .9rem;border-radius:20px;font-size:.75rem;font-weight:700;">
        Etapa: Investigación
    </span>
</div>

{{-- Banner si ya tomó decisión --}}
@if($decisionCfg)
<div style="background:{{ $decisionCfg['bg'] }};border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;">
    <span style="font-size:1.1rem;">{{ $inv->owner_decision === 'approved' ? '✓' : ($inv->owner_decision === 'declined' ? '✕' : '→') }}</span>
    <div>
        <div style="font-size:.85rem;font-weight:700;color:{{ $decisionCfg['color'] }};">{{ $decisionCfg['label'] }}</div>
        @if($inv->owner_decision_notes)
        <div style="font-size:.8rem;color:{{ $decisionCfg['color'] }};opacity:.8;margin-top:.2rem;">Tu nota: "{{ $inv->owner_decision_notes }}"</div>
        @endif
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">

    {{-- Bloque 1: Perfil del candidato --}}
    <div class="card" style="grid-column:1 / -1;">
        <div class="card-header">
            <h3>&#128100; Perfil del candidato</h3>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem 1.5rem;">
            <div>
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.25rem;">Ocupación</div>
                <div style="font-size:.92rem;font-weight:600;color:var(--text);">{{ $inv->occupation ?: '—' }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.25rem;">Empresa / Empleador</div>
                <div style="font-size:.92rem;font-weight:600;color:var(--text);">{{ $inv->employer ?: '—' }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.25rem;">Antigüedad</div>
                <div style="font-size:.92rem;font-weight:600;color:var(--text);">
                    @if($inv->employment_years !== null)
                        {{ $inv->employment_years }} {{ $inv->employment_years === 1 ? 'año' : 'años' }}
                    @else —
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.25rem;">Tipo de ingreso</div>
                <div style="font-size:.92rem;font-weight:600;color:var(--text);">{{ $inv->income_type_label }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.25rem;">Ingreso mensual</div>
                <div style="font-size:.92rem;font-weight:600;color:var(--text);">
                    @if($inv->monthly_income)
                        ${{ number_format($inv->monthly_income, 0) }} MXN
                        @if($inv->income_verified)
                            <span style="font-size:.7rem;background:#dcfce7;color:#166534;padding:.1rem .4rem;border-radius:4px;margin-left:.3rem;font-weight:700;">Verificado ✓</span>
                        @endif
                    @else —
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bloque 2: Perfil financiero --}}
    <div class="card">
        <div class="card-header">
            <h3>&#128176; Perfil financiero</h3>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">

            {{-- Ratio renta/ingreso --}}
            @if($ratio !== null)
            <div style="background:#f8fafc;border-radius:10px;padding:1rem;">
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.5rem;">
                    Relación renta / ingreso
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div style="font-size:1.6rem;font-weight:800;color:{{ $ratioColor }};">{{ $ratio }}%</div>
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:{{ $ratioColor }};">{{ $ratioLabel }}</div>
                        <div style="font-size:.72rem;color:var(--text-muted);">
                            @if($ratio <= 35) Destina menos del 35% de su ingreso a renta. Ideal.
                            @elseif($ratio <= 50) Entre 35–50%. Dentro del rango aceptable.
                            @else Más del 50% de su ingreso va a renta. Considera con cuidado.
                            @endif
                        </div>
                    </div>
                </div>
                {{-- barra visual --}}
                <div style="margin-top:.75rem;background:#e2e8f0;border-radius:6px;height:8px;overflow:hidden;">
                    <div style="height:8px;border-radius:6px;background:{{ $ratioColor }};width:{{ min($ratio, 100) }}%;transition:width .4s;"></div>
                </div>
            </div>
            @endif

            {{-- Historial crediticio --}}
            <div>
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.4rem;">Historial crediticio</div>
                @if($inv->credit_status)
                <div style="display:inline-flex;align-items:center;gap:.4rem;background:{{ $inv->credit_status_color }}22;border-radius:8px;padding:.4rem .75rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $inv->credit_status_color }};flex-shrink:0;display:inline-block;"></span>
                    <span style="font-size:.88rem;font-weight:700;color:{{ $inv->credit_status_color }};">{{ $inv->credit_status_label }}</span>
                    @if($inv->bureau_checked)
                        <span style="font-size:.7rem;color:var(--text-muted);">· Buró consultado ✓</span>
                    @endif
                </div>
                @else
                <span style="font-size:.88rem;color:var(--text-muted);">—</span>
                @endif
                @if($inv->credit_notes)
                <p style="font-size:.8rem;color:var(--text-muted);margin:.5rem 0 0;line-height:1.5;">{{ $inv->credit_notes }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloque 3: Documentos y referencias --}}
    <div class="card">
        <div class="card-header">
            <h3>&#10003; Documentos y referencias</h3>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">
            @php
                $docs = [
                    ['label' => 'Identificación oficial', 'ok' => true],
                    ['label' => 'Comprobante de ingresos', 'ok' => $inv->income_verified],
                    ['label' => 'Reporte crediticio / Buró', 'ok' => $inv->bureau_checked],
                    ['label' => "Referencias ({$inv->references_count})", 'ok' => $inv->references_ok],
                ];
            @endphp
            @foreach($docs as $doc)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:.85rem;color:var(--text);">{{ $doc['label'] }}</span>
                @if($doc['ok'])
                    <span style="background:#dcfce7;color:#166534;font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;">Verificado ✓</span>
                @else
                    <span style="background:#f1f5f9;color:#94a3b8;font-size:.72rem;font-weight:600;padding:.2rem .55rem;border-radius:20px;">Pendiente</span>
                @endif
            </div>
            @endforeach

            @if($inv->references_notes)
            <p style="font-size:.8rem;color:var(--text-muted);margin:.25rem 0 0;line-height:1.5;">{{ $inv->references_notes }}</p>
            @endif

            {{-- Póliza jurídica --}}
            @if($rental->poliza)
            @php $poliza = $rental->poliza; @endphp
            <div style="margin-top:.5rem;background:#f8fafc;border-radius:10px;padding:.75rem 1rem;">
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.4rem;">Póliza Jurídica</div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $poliza->status_color }};flex-shrink:0;display:inline-block;"></span>
                    <span style="font-size:.88rem;font-weight:600;color:var(--text);">{{ $poliza->status_label }}</span>
                    @if($poliza->insurance_company)
                        <span style="font-size:.78rem;color:var(--text-muted);">· {{ $poliza->insurance_company }}</span>
                    @endif
                </div>
            </div>
            @elseif($rental->guarantee_type === 'deposito')
            <div style="margin-top:.5rem;background:#f8fafc;border-radius:10px;padding:.75rem 1rem;">
                <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);margin-bottom:.4rem;">Garantía</div>
                <span style="font-size:.88rem;font-weight:600;color:var(--text);">Depósito</span>
                @if($rental->deposit_amount)
                    <span style="font-size:.8rem;color:var(--text-muted);margin-left:.35rem;">${{ number_format($rental->deposit_amount, 0) }} MXN</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Bloque 4: Opinión del asesor --}}
    @if($inv->asesor_notes || $inv->asesor_recommendation)
    <div class="card" style="grid-column:1 / -1;">
        <div class="card-header">
            <h3>&#128203; Opinión de tu asesor</h3>
            @if($inv->asesor_recommendation)
            <span style="background:{{ $recBg }};color:{{ $recColor }};font-size:.72rem;font-weight:700;padding:.25rem .7rem;border-radius:20px;">
                {{ $inv->recommendation_label }}
            </span>
            @endif
        </div>
        @if($inv->asesor_notes)
        <div class="card-body">
            <div style="border-left:3px solid {{ $recColor }};background:{{ $recBg }};border-radius:0 10px 10px 0;padding:1rem 1.25rem;">
                <p style="margin:0;font-size:.9rem;color:var(--text);line-height:1.7;">{{ $inv->asesor_notes }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

</div>{{-- /grid --}}

{{-- Bloque 5: Decisión del propietario --}}
@if(!$alreadyDone)
<div class="card" style="margin-top:.5rem;border:2px solid #e0e7ff;">
    <div class="card-header" style="background:#f5f3ff;">
        <h3 style="color:#3730a3;">&#9998; Tu decisión</h3>
        <span style="font-size:.78rem;color:#6366f1;font-weight:500;">Esta acción notificará a tu asesor de inmediato</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('portal.rentals.investigacion.decision', $rental->id) }}" id="decision-form">
            @csrf

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.25rem;">
                {{-- Aprobar --}}
                <label style="cursor:pointer;">
                    <input type="radio" name="owner_decision" value="approved" style="display:none;" onchange="selectDecision(this)">
                    <div class="decision-card" data-value="approved"
                         style="border:2px solid var(--border);border-radius:12px;padding:1rem;text-align:center;transition:all .15s;">
                        <div style="font-size:1.5rem;margin-bottom:.4rem;">✓</div>
                        <div style="font-size:.85rem;font-weight:700;color:#166534;">Aprobar candidato</div>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">Avanzamos a la etapa de contrato</div>
                    </div>
                </label>

                {{-- Más información --}}
                <label style="cursor:pointer;">
                    <input type="radio" name="owner_decision" value="more_info" style="display:none;" onchange="selectDecision(this)">
                    <div class="decision-card" data-value="more_info"
                         style="border:2px solid var(--border);border-radius:12px;padding:1rem;text-align:center;transition:all .15s;">
                        <div style="font-size:1.5rem;margin-bottom:.4rem;">?</div>
                        <div style="font-size:.85rem;font-weight:700;color:#92400e;">Necesito más información</div>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">Tu asesor te contactará pronto</div>
                    </div>
                </label>

                {{-- Declinar --}}
                <label style="cursor:pointer;">
                    <input type="radio" name="owner_decision" value="declined" style="display:none;" onchange="selectDecision(this)">
                    <div class="decision-card" data-value="declined"
                         style="border:2px solid var(--border);border-radius:12px;padding:1rem;text-align:center;transition:all .15s;">
                        <div style="font-size:1.5rem;margin-bottom:.4rem;">✕</div>
                        <div style="font-size:.85rem;font-weight:700;color:#991b1b;">Declinar candidato</div>
                        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem;">Seguiremos buscando</div>
                    </div>
                </label>
            </div>

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label">Nota para tu asesor <span style="font-weight:400;color:var(--text-muted);">(opcional)</span></label>
                <textarea name="owner_decision_notes" class="form-textarea" rows="3" maxlength="800"
                          placeholder="¿Tienes alguna duda, condición o comentario para tu asesor?"></textarea>
            </div>

            <button type="submit" id="btn-submit" class="btn btn-primary" disabled
                    style="width:100%;justify-content:center;font-size:.95rem;padding:.7rem;">
                Enviar mi decisión
            </button>
        </form>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
.decision-card:hover {
    border-color: var(--primary) !important;
    background: #f5f3ff;
}
.decision-card.selected-approved  { border-color:#10b981 !important; background:#dcfce7; }
.decision-card.selected-declined  { border-color:#ef4444 !important; background:#fee2e2; }
.decision-card.selected-more_info { border-color:#f59e0b !important; background:#fef3c7; }
</style>
@endsection

@push('scripts')
<script>
function selectDecision(radio) {
    document.querySelectorAll('.decision-card').forEach(c => {
        c.className = 'decision-card';
        c.style.cssText = 'border:2px solid var(--border);border-radius:12px;padding:1rem;text-align:center;transition:all .15s;';
    });
    var card = document.querySelector('.decision-card[data-value="' + radio.value + '"]');
    if (card) card.classList.add('selected-' + radio.value);
    document.getElementById('btn-submit').disabled = false;
}
</script>
@endpush
