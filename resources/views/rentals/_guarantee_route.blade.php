{{-- Ruta de garantía del inquilino (póliza o aval) + lo que él ve. Espera: $rental. Fuente: App\Support\TenantRoadmap --}}
@php
    $rm = \App\Support\TenantRoadmap::build($rental);
    $route = $rm['route'];
    $garantia = collect($rm['steps'])->firstWhere('key', 'garantia');
    $plan = $rental->polizaPlan;
    $stateColor = ['done' => '#10b981', 'active' => '#1D4ED8', 'pending' => '#f59e0b', 'todo' => '#94a3b8'];
@endphp
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body" style="padding:1rem;">
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.6rem;">
            <h4 style="font-size:.9rem;font-weight:700;margin:0;">🛡️ Garantía del inquilino</h4>
            @if($route === 'poliza')<span class="badge badge-purple">Póliza jurídica</span>
            @elseif($route === 'aval')<span class="badge badge-blue">Aval + investigación</span>
            @else<span class="badge badge-yellow">Sin definir</span>@endif
            @if($rental->guarantee_declared_at)
                <span style="font-size:.72rem;color:var(--text-muted);">definida el {{ $rental->guarantee_declared_at->format('d/m/Y') }}</span>
            @endif
        </div>

        <p style="font-size:.82rem;margin:0 0 .6rem;color:var(--text);">{{ $garantia['summary'] }}</p>

        @if($route === 'poliza' && $plan)
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:.7rem .9rem;font-size:.82rem;margin-bottom:.6rem;">
                <strong>Plan elegido:</strong> {{ $plan->name }} — {{ $plan->price_formatted }} ({{ $plan->provider_name }})
                @if($rental->poliza_plan_selected_at) · elegido el {{ $rental->poliza_plan_selected_at->format('d/m/Y') }}@endif
                <div style="margin-top:.4rem;color:#6b21a8;">
                    <strong>Te toca:</strong> 1) tramitar el alta con {{ $plan->provider_name }} · 2) ir actualizando el estado en la pestaña <a href="javascript:void(0)" onclick="switchTab('poliza')" style="color:#6b21a8;text-decoration:underline;">Póliza</a> · 3) cuando lo emitan, subir <strong>su contrato</strong> en la pestaña <a href="javascript:void(0)" onclick="switchTab('contracts')" style="color:#6b21a8;text-decoration:underline;">Contratos</a>.
                    El inquilino paga la póliza directo al proveedor.
                </div>
            </div>
        @elseif($route === 'poliza')
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;margin-bottom:.6rem;">
                El inquilino aún no elige plan. @if($rm['plans']->isEmpty())<strong>No hay planes activos con precio</strong> — configúralos en <a href="{{ route('poliza-plans.index') }}">Planes de póliza</a>.@endif
            </div>
        @elseif($route === 'aval')
            <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.6rem;">
                Cuota de investigación ${{ number_format(\App\Support\TenantRoadmap::INVESTIGATION_FEE) }} MXN:
                @if($rental->investigacion_paid_at)<strong style="color:#166534;">pagada el {{ $rental->investigacion_paid_at->format('d/m/Y') }}</strong>@else<strong style="color:#92400e;">pendiente de pago</strong> (regístrala abajo, en "Cuota de investigación")@endif
            </div>
        @endif

        <div style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;border-top:1px dashed var(--border);padding-top:.6rem;">
            <span style="font-size:.72rem;color:var(--text-muted);">Lo que ve el inquilino:</span>
            @foreach($rm['steps'] as $s)
                <span style="font-size:.7rem;font-weight:600;padding:.15rem .55rem;border-radius:999px;background:{{ $stateColor[$s['state']] }}20;color:{{ $stateColor[$s['state']] }};">{{ $s['state'] === 'done' ? '✓ ' : '' }}{{ $s['title'] }}</span>
            @endforeach
            <span style="margin-left:auto;display:flex;gap:.35rem;">
                <form method="POST" action="{{ route('rentals.guarantee-route', $rental->id) }}" onsubmit="return confirm('¿Cambiar la ruta a póliza jurídica?')">@csrf<input type="hidden" name="route" value="poliza"><button class="btn btn-sm btn-outline" {{ $route === 'poliza' ? 'disabled' : '' }}>Fijar: póliza</button></form>
                <form method="POST" action="{{ route('rentals.guarantee-route', $rental->id) }}" onsubmit="return confirm('¿Cambiar la ruta a aval + investigación?')">@csrf<input type="hidden" name="route" value="aval"><button class="btn btn-sm btn-outline" {{ $route === 'aval' ? 'disabled' : '' }}>Fijar: aval</button></form>
            </span>
        </div>
    </div>
</div>
