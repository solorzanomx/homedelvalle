{{-- El PROPIETARIO elige la póliza de su inquilino y cómo se reparte el costo. Espera: $rental. Fuente: PolizaPricing / TenantRoadmap --}}
@php
    $route = \App\Support\TenantRoadmap::route($rental);
    $rent = (float) $rental->monthly_rent;
    $decision = \App\Support\TenantRoadmap::polizaDecision($rental);
    $approved = $rental->poliza && $rental->poliza->status === 'approved';
    $tenantName = $rental->tenantClient?->name ?? 'tu inquilino';
@endphp

@if($rental->tenant_client_id && $route === 'poliza')
@php
    $plans = \App\Models\PolizaPlan::offered()->with('coverages')->get();
    $quotes = \App\Support\PolizaPricing::quotes($rent);
    $sheet = \App\Support\PolizaPricing::sheet();
    $editing = ! $decision || request()->boolean('cambiar');
    $currentPlanId = $decision['plan']->id ?? null;
    $currentShare = (int) ($rental->poliza_tenant_share ?? 100);
@endphp
<div id="poliza-dueno" style="background:#fff;border:2px solid {{ $decision ? '#10b981' : '#1D4ED8' }};border-radius:16px;padding:1.1rem 1.15rem;margin-bottom:1.25rem;">
    <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.35rem;">
        <span style="font-size:1.25rem;">🛡️</span>
        <h3 style="margin:0;font-size:1.02rem;color:#0f172a;flex:1;">Póliza jurídica de {{ $tenantName }}</h3>
        @if($decision)<span style="font-size:.68rem;font-weight:800;padding:.2rem .6rem;border-radius:9999px;background:#ecfdf5;color:#047857;">ELEGIDA</span>
        @else<span style="font-size:.68rem;font-weight:800;padding:.2rem .6rem;border-radius:9999px;background:#1D4ED8;color:#fff;">TE TOCA ELEGIR</span>@endif
    </div>
    <p style="margin:0 0 .8rem;font-size:.82rem;color:#475569;line-height:1.5;">
        Tu inquilino no tiene aval con propiedad en CDMX, así que su garantía es una póliza jurídica de Previsión Legal. <strong>Tú decides el plan y cómo se reparte el costo.</strong>
    </p>

    @if($rent <= 0)
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.7rem .85rem;font-size:.8rem;color:#92400e;">Aún no está capturada la renta mensual del trato; tu asesor la confirmará para mostrarte las tarifas.</div>
    @else
        @if($decision && ! $editing)
            @php $dd = $decision; @endphp
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:.85rem 1rem;font-size:.85rem;color:#14532d;line-height:1.55;">
                <strong>Plan {{ $dd['plan']->name }}</strong> — ${{ number_format($dd['amount']) }} MXN<br>
                @if($dd['split']['tenant_pct'] === 100) La paga tu inquilino (100%).
                @else Mitad y mitad: ${{ number_format($dd['split']['tenant']) }} tu inquilino y ${{ number_format($dd['split']['owner']) }} tú.
                @endif
                <br><span style="font-size:.76rem;color:#166534;">
                    @if($dd['payment_mode'] === 'hdv') Tu asesor te indicará cómo pagar tu parte. @else Cada quien paga su parte directo a Previsión Legal. @endif
                    Gastos de emisión: ${{ number_format($dd['emission_fee']) }} (se acreditan al precio si se concreta).
                </span>
            </div>
            @if(! $approved)
            <a href="{{ route('portal.rentals.show', ['id' => $rental->id, 'cambiar' => 1]) }}#poliza-dueno" style="display:inline-block;margin-top:.6rem;font-size:.8rem;font-weight:700;color:#1D4ED8;">Cambiar mi elección</a>
            @endif
        @else
            <form method="POST" action="{{ route('portal.rentals.poliza.decide', $rental->id) }}" id="polizaForm">
                @csrf
                <div style="font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#64748b;margin-bottom:.45rem;">1. Elige el plan (según tu renta de ${{ number_format($rent) }})</div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.7rem;margin-bottom:1rem;">
                    @foreach($plans as $plan)
                        @php $q = $quotes[$plan->id] ?? null; @endphp
                        @continue(! $q)
                        <label style="position:relative;display:flex;flex-direction:column;border:2px solid {{ $currentPlanId === $plan->id ? '#1D4ED8' : '#e2e8f0' }};border-radius:14px;padding:.85rem;cursor:pointer;background:#fff;">
                            @if($plan->tagline)<span style="position:absolute;top:-.6rem;left:.8rem;background:#1D4ED8;color:#fff;font-size:.62rem;font-weight:700;padding:.12rem .55rem;border-radius:9999px;">{{ $plan->tagline }}</span>@endif
                            <span style="display:flex;align-items:center;gap:.5rem;">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}" data-amount="{{ $q['amount'] }}" required style="width:20px;height:20px;" {{ $currentPlanId === $plan->id ? 'checked' : '' }}>
                                <strong style="font-size:1rem;color:#0f172a;">{{ $plan->name }}</strong>
                            </span>
                            <span style="font-size:1.4rem;font-weight:800;color:#1D4ED8;margin:.3rem 0 .1rem;">${{ number_format($q['amount']) }} <span style="font-size:.7rem;font-weight:600;color:#64748b;">MXN</span></span>
                            @if($q['basis'] === 'percent')<span style="font-size:.7rem;color:#64748b;">{{ rtrim(rtrim(number_format($q['percent'], 2), '0'), '.') }}% de tu renta mensual</span>@endif
                            @if($plan->description)<span style="font-size:.76rem;color:#475569;margin:.4rem 0;line-height:1.4;">{{ $plan->description }}</span>@endif
                            <details style="font-size:.76rem;margin-top:auto;">
                                <summary style="cursor:pointer;color:#1D4ED8;font-weight:700;">Qué cubre ({{ $plan->includedCoverages()->count() }} conceptos)</summary>
                                <ul style="margin:.4rem 0 0;padding:0;list-style:none;line-height:1.45;color:#334155;">
                                    @foreach($plan->includedCoverages() as $cov)<li>✓ {{ $cov->label }}@if($cov->note) <span style="color:#94a3b8;">*</span>@endif</li>@endforeach
                                </ul>
                            </details>
                        </label>
                    @endforeach
                </div>

                <div style="font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#64748b;margin-bottom:.45rem;">2. ¿Quién la paga?</div>
                <div style="display:grid;gap:.5rem;margin-bottom:1rem;">
                    @foreach(\App\Support\PolizaPricing::SHARE_OPTIONS as $pct => $label)
                    <label style="display:flex;align-items:center;gap:.65rem;border:2px solid #e2e8f0;border-radius:12px;padding:.75rem .9rem;cursor:pointer;min-height:52px;">
                        <input type="radio" name="tenant_share" value="{{ $pct }}" required style="width:20px;height:20px;" {{ $currentShare === $pct ? 'checked' : '' }}>
                        <span style="flex:1;font-size:.88rem;font-weight:600;color:#0f172a;">{{ $label }}</span>
                        <span class="share-amt" data-pct="{{ $pct }}" style="font-size:.78rem;color:#64748b;text-align:right;"></span>
                    </label>
                    @endforeach
                </div>

                <button type="submit" style="width:100%;min-height:50px;border:0;border-radius:12px;background:#1D4ED8;color:#fff;font-weight:800;font-size:1rem;cursor:pointer;">Confirmar póliza</button>
                <p style="font-size:.72rem;color:#94a3b8;margin:.6rem 0 0;line-height:1.5;">
                    Tarifas de referencia de la {{ $sheet?->name ?? 'hoja de servicios' }} ({{ $sheet?->zone_label }}); pueden cambiar. Al iniciar el trámite se cubre un anticipo por gastos de emisión de ${{ number_format($sheet?->emission_fee ?? 0) }}: se acredita al precio si se concreta y no se reembolsa si no.
                    <br>* Sujeto a disponibilidad o previa cita.
                </p>
            </form>
            <script>
            (function () {
                var form = document.getElementById('polizaForm');
                var fmt = function (n) { return '$' + Math.round(n).toLocaleString('es-MX'); };
                function refresh() {
                    var p = form.querySelector('input[name=plan_id]:checked');
                    form.querySelectorAll('.share-amt').forEach(function (el) {
                        if (!p) { el.textContent = 'elige un plan'; return; }
                        var a = parseFloat(p.dataset.amount), pct = parseInt(el.dataset.pct, 10);
                        el.textContent = pct === 100 ? 'inquilino ' + fmt(a) : 'cada uno ' + fmt(a / 2);
                    });
                }
                form.addEventListener('change', refresh); refresh();
            })();
            </script>
        @endif
    @endif
</div>
@elseif($rental->tenant_client_id && $route === 'undecided')
<div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:14px;padding:.8rem 1rem;margin-bottom:1.25rem;font-size:.82rem;color:#475569;line-height:1.5;">
    🛡️ <strong>Garantía de tu inquilino:</strong> cuando defina si tiene un aval con propiedad en CDMX, aquí verás qué sigue. Si no lo tiene, te tocará elegir su póliza jurídica.
</div>
@endif
