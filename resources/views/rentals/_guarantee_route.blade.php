{{-- Ruta de garantía del inquilino (póliza o aval) + lo que él ve. Espera: $rental. Fuente: App\Support\TenantRoadmap --}}
@php
    $rm = \App\Support\TenantRoadmap::build($rental);
    $route = $rm['route'];
    $garantia = collect($rm['steps'])->firstWhere('key', 'garantia');
    $stateColor = ['done' => '#10b981', 'active' => '#1D4ED8', 'pending' => '#f59e0b', 'todo' => '#94a3b8'];
@endphp
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body" style="padding:1rem;">
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:.6rem;">
            <h4 style="font-size:.9rem;font-weight:700;margin:0;">🛡️ Garantía del inquilino</h4>
            @if($route === 'poliza')<span class="badge badge-purple">Póliza jurídica</span>
                {{-- Obligado solidario: se pide cuando la garantía es póliza (sin aval); mismos datos y documentos que el inquilino --}}
            @php
                $osSvc = app(\App\Services\ObligadoSolidarioService::class);
                $osReq = $osSvc->isRequired($rental);
                $osSt = $osSvc->status($rental);
            @endphp
            <div style="border-top:1px dashed var(--border);padding-top:.7rem;margin-top:.4rem;">
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem;">
                    <strong style="font-size:.85rem;">🤝 Obligado solidario</strong>
                    @if(! $osReq)<span class="badge badge-blue">Exento en este trato</span>
                    @elseif($osSt['complete'])<span class="badge badge-green">Completo y aprobado</span>
                    @elseif($osSt['registered'])<span class="badge badge-yellow">En proceso</span>
                    @else<span class="badge badge-red">Falta registrarlo</span>@endif
                    <form method="POST" action="{{ route('rentals.obligado.toggle', $rental->id) }}" style="margin-left:auto;" onsubmit="return confirm('{{ $osReq ? '¿Exentar al obligado solidario en este trato?' : '¿Volver a requerirlo?' }}')">@csrf
                        <input type="hidden" name="required" value="{{ $osReq ? 0 : 1 }}"><button class="btn btn-sm btn-outline">{{ $osReq ? 'No requerido en este trato' : 'Volver a requerirlo' }}</button></form>
                </div>
                @if($osReq)
                    @if($osSt['registered'])
                        <div style="font-size:.82rem;line-height:1.55;">
                            <strong>{{ $osSt['name'] }}</strong> · {{ $osSt['client']->phone }}@if($osSt['client']->email) · {{ $osSt['client']->email }}@endif · <span style="color:var(--text-muted);">lo captura el inquilino desde su Portal</span><br>
                            Datos {{ $osSt['data_pct'] }}% · Documentos: {{ $osSt['docs']['aprobado'] }} aprobados, {{ $osSt['docs']['revision'] }} en revisión, {{ $osSt['docs']['corregir'] }} por corregir, {{ $osSt['docs']['falta'] }} por subir
                            @if($osSt['docs_missing'])<br><span style="color:#92400e;">Falta aprobar: {{ implode(' · ', $osSt['docs_missing']) }}</span>@endif
                            <div style="margin-top:.4rem;display:flex;gap:.4rem;flex-wrap:wrap;">
                                <a class="btn btn-sm btn-outline" href="{{ route('clients.show', $osSt['client']->id) }}">Ver ficha</a>
                                <a class="btn btn-sm btn-outline" href="javascript:void(0)" onclick="switchTab('documents')">Ver sus documentos</a>
                            </div>
                        </div>
                    @endif
                    <details style="margin-top:.5rem;" {{ $osSt['registered'] ? '' : 'open' }}>
                        <summary style="cursor:pointer;font-size:.8rem;font-weight:700;color:var(--primary);">{{ $osSt['registered'] ? 'Cambiar a otra persona' : 'Registrarlo a nombre del inquilino' }}</summary>
                        <form method="POST" action="{{ route('rentals.obligado.register', $rental->id) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;margin-top:.5rem;">@csrf
                            <div class="form-group" style="margin:0;min-width:170px;"><label class="form-label" style="font-size:.72rem;">Nombre completo</label><input name="name" class="form-input" required></div>
                            <div class="form-group" style="margin:0;min-width:150px;"><label class="form-label" style="font-size:.72rem;">Celular</label><input name="phone" type="tel" class="form-input" required></div>
                            <div class="form-group" style="margin:0;min-width:190px;"><label class="form-label" style="font-size:.72rem;">Correo (opcional)</label><input name="email" type="email" class="form-input"></div>
                            <button class="btn btn-sm btn-primary">Registrar</button>
                        </form>
                    </details>
                @endif
            </div>
        @elseif($route === 'aval')<span class="badge badge-blue">Aval + investigación</span>
            @else<span class="badge badge-yellow">Sin definir</span>@endif
            @if($rental->guarantee_declared_at)
                <span style="font-size:.72rem;color:var(--text-muted);">definida el {{ $rental->guarantee_declared_at->format('d/m/Y') }}</span>
            @endif
        </div>

        <p style="font-size:.82rem;margin:0 0 .6rem;color:var(--text);">{{ $garantia['summary'] }}</p>

        @if($route === 'poliza')
        @php
            $dec = \App\Support\TenantRoadmap::polizaDecision($rental);
            $rent = (float) $rental->monthly_rent;
            $offered = \App\Models\PolizaPlan::offered()->get();
            $quotes = \App\Support\PolizaPricing::quotes($rent);
        @endphp
            @if($dec)
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:.7rem .9rem;font-size:.82rem;margin-bottom:.6rem;">
                <strong>Plan {{ $dec['plan']->name }}</strong> — ${{ number_format($dec['amount']) }} ({{ $dec['plan']->provider_name }})
                · {{ $dec['decided_by'] === 'owner' ? 'elegido por el propietario' : ($dec['decided_by'] === 'advisor' ? 'registrado por un asesor' : 'elegido por el inquilino (flujo anterior)') }}
                @if($rental->poliza_plan_selected_at) el {{ $rental->poliza_plan_selected_at->format('d/m/Y') }}@endif
                <div style="margin-top:.3rem;">
                    Reparto: <strong>inquilino ${{ number_format($dec['split']['tenant']) }} ({{ $dec['split']['tenant_pct'] }}%)</strong>
                    @if($dec['split']['owner_pct'] > 0) · <strong>propietario ${{ number_format($dec['split']['owner']) }} ({{ $dec['split']['owner_pct'] }}%)</strong>@endif
                    · gastos de emisión ${{ number_format($dec['emission_fee']) }} (se acreditan al precio si se concreta)
                </div>
                <div style="margin-top:.4rem;color:#6b21a8;">
                    <strong>Te toca:</strong> 1) tramitar el alta con {{ $dec['plan']->provider_name }} · 2) actualizar el estado en la pestaña <a href="javascript:void(0)" onclick="switchTab('poliza')" style="color:#6b21a8;text-decoration:underline;">Póliza</a> · 3) al emitirse, subir <strong>su contrato</strong> en <a href="javascript:void(0)" onclick="switchTab('contracts')" style="color:#6b21a8;text-decoration:underline;">Contratos</a>.
                </div>
            </div>
            @else
            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.6rem .9rem;font-size:.8rem;margin-bottom:.6rem;display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                <span style="flex:1;">⏳ <strong>El propietario aún no elige la póliza</strong> (plan y reparto del costo).@if($rent <= 0) <strong>Falta la renta mensual del trato</strong> para cotizar.@endif</span>
                <form method="POST" action="{{ route('rentals.poliza-remind-owner', $rental->id) }}">@csrf<button class="btn btn-sm btn-outline">✉️ Recordárselo</button></form>
            </div>
            @endif

            @if($rent > 0)
            <details style="margin-bottom:.6rem;" {{ $dec ? '' : 'open' }}>
                <summary style="cursor:pointer;font-size:.8rem;font-weight:700;color:var(--primary);">{{ $dec ? 'Cambiar la decisión (a nombre del propietario)' : 'Decidir a nombre del propietario' }}</summary>
                <form method="POST" action="{{ route('rentals.poliza-decision', $rental->id) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;margin-top:.5rem;">
                    @csrf
                    <div class="form-group" style="margin:0;min-width:200px;">
                        <label class="form-label" style="font-size:.72rem;">Plan (renta ${{ number_format($rent) }})</label>
                        <select name="plan_id" class="form-select" required>
                            @foreach($offered as $pl) @php $qq = $quotes[$pl->id] ?? null; @endphp
                                @if($qq)<option value="{{ $pl->id }}" {{ $dec && $dec['plan']->id === $pl->id ? 'selected' : '' }}>{{ $pl->name }} — ${{ number_format($qq['amount']) }}</option>@endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;min-width:200px;">
                        <label class="form-label" style="font-size:.72rem;">¿Quién la paga?</label>
                        <select name="tenant_share" class="form-select" required>
                            @foreach(\App\Support\PolizaPricing::SHARE_OPTIONS as $pct => $lb)<option value="{{ $pct }}" {{ (int) ($rental->poliza_tenant_share ?? 100) === $pct ? 'selected' : '' }}>{{ $lb }}</option>@endforeach
                        </select>
                    </div>
                    <button class="btn btn-sm btn-primary">Registrar decisión</button>
                </form>
            </details>
            @endif

            @if($dec)
            <form method="POST" action="{{ route('rentals.poliza-payment', $rental->id) }}" style="display:flex;gap:.9rem;flex-wrap:wrap;align-items:center;font-size:.8rem;margin-bottom:.6rem;">
                @csrf
                <label style="display:flex;gap:.35rem;align-items:center;">Pago:
                    <select name="payment_mode" class="form-select" style="min-height:32px;padding:.15rem .4rem;">
                        <option value="direct" {{ $dec['payment_mode'] === 'direct' ? 'selected' : '' }}>Cada parte paga directo a Previsión Legal</option>
                        <option value="hdv" {{ $dec['payment_mode'] === 'hdv' ? 'selected' : '' }}>Home del Valle cobra y liquida</option>
                    </select>
                </label>
                <label style="display:flex;gap:.3rem;align-items:center;"><input type="checkbox" name="tenant_paid" value="1" {{ $dec['tenant_paid'] ? 'checked' : '' }}> Inquilino pagó su parte</label>
                @if($dec['split']['owner_pct'] > 0)<label style="display:flex;gap:.3rem;align-items:center;"><input type="checkbox" name="owner_paid" value="1" {{ $dec['owner_paid'] ? 'checked' : '' }}> Propietario pagó su parte</label>@endif
                <button class="btn btn-sm btn-outline">Guardar</button>
            </form>
            @endif
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
