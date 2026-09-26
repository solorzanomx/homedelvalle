{{-- "¿Qué sigue?" del inquilino. Espera: $rental (RentalProcess donde el cliente es el inquilino). Fuente: App\Support\TenantRoadmap --}}
@php
    $rm = $roadmap ?? \App\Support\TenantRoadmap::build($rental);
    $compact = $compact ?? false;   // compacto: pasos hechos = una línea, pasos futuros = solo título, el activo se expande
    $icons = ['apartado' => '🔑', 'informacion' => '📝', 'documentos' => '📄', 'obligado' => '🤝', 'revision' => '🔎', 'garantia' => '🛡️', 'contrato' => '✍️', 'entrega' => '🏠'];
@endphp
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.1rem 1.25rem;margin-bottom:1rem;">
    @if(! $compact)
    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.25rem;">
        <h3 style="margin:0;font-size:1rem;color:#0f172a;">Tu camino a la renta</h3>
        <span style="font-size:.72rem;color:#64748b;">{{ collect($rm['steps'])->where('state','done')->count() }} de {{ count($rm['steps']) }} pasos completos</span>
    </div>
    <p style="margin:0 0 .9rem;font-size:.78rem;color:#64748b;">Siempre sabrás en qué paso vas y qué sigue.</p>
    @else
    <h3 style="margin:0 0 .8rem;font-size:.9rem;color:#0f172a;">Todo el camino</h3>
    @endif

    @foreach($rm['steps'] as $i => $step)
        @php
            $st = $step['state'];
            $dot = ['done' => ['#10b981', '#ecfdf5'], 'active' => ['#1D4ED8', '#eff6ff'], 'pending' => ['#f59e0b', '#fffbeb'], 'todo' => ['#cbd5e1', '#f8fafc']][$st];
        @endphp
        @php $slim = $compact && $st !== 'active'; @endphp
        <div id="step-{{ $step['key'] }}" style="display:flex;gap:.8rem;{{ $loop->last ? '' : ($slim ? 'padding-bottom:.7rem;' : 'padding-bottom:1rem;') }}">
            <div style="display:flex;flex-direction:column;align-items:center;">
                <div style="width:30px;height:30px;border-radius:50%;background:{{ $dot[1] }};border:2px solid {{ $dot[0] }};display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;">
                    {{ $st === 'done' ? '✓' : ($icons[$step['key']] ?? ($i + 1)) }}
                </div>
                @if(! $loop->last)<div style="flex:1;width:2px;background:{{ $st === 'done' ? '#10b981' : '#e2e8f0' }};margin-top:2px;"></div>@endif
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.88rem;font-weight:700;color:{{ in_array($st, ['todo']) ? '#94a3b8' : '#0f172a' }};">
                    {{ $step['title'] }}
                    @if($st === 'active')<span style="font-size:.62rem;font-weight:700;background:#1D4ED8;color:#fff;border-radius:9999px;padding:.1rem .5rem;margin-left:.3rem;vertical-align:middle;">AHORA</span>@elseif($st === 'pending')<span style="font-size:.62rem;font-weight:700;background:#f59e0b;color:#fff;border-radius:9999px;padding:.1rem .5rem;margin-left:.3rem;vertical-align:middle;">PENDIENTE</span>@endif
                </div>
                @if(! $slim)
                <div style="font-size:.8rem;color:{{ $st === 'todo' ? '#94a3b8' : '#475569' }};margin-top:.15rem;line-height:1.45;">{{ $step['summary'] }}</div>
                @endif

                {{-- Tus datos / Documentos: enlace al lugar de trabajo --}}
                @if(! $compact && $step['key'] === 'informacion' && $st === 'active')
                    <a href="{{ route('portal.expediente') }}" style="display:inline-block;margin-top:.5rem;font-size:.78rem;font-weight:700;color:#1D4ED8;">Completar mis datos →</a>
                @endif
                @if(! $compact && $step['key'] === 'documentos' && $st === 'active')
                    <a href="{{ route('portal.documents.index') }}" style="display:inline-block;margin-top:.5rem;font-size:.78rem;font-weight:700;color:#1D4ED8;">Ir a mis documentos →</a>
                @endif

                {{-- Garantía: ¿tienes aval en CDMX? --}}
                @if($step['key'] === 'garantia' && ($step['action'] ?? null) === 'declare' && ! $slim)
                    <form method="POST" action="{{ route('portal.rentals.guarantee.declare', $rental->id) }}" style="margin-top:.6rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                        @csrf
                        <button name="has_aval" value="1" class="btn btn-sm" style="border:1.5px solid #1D4ED8;background:#fff;color:#1D4ED8;border-radius:9px;padding:.55rem .9rem;font-weight:700;font-size:.8rem;cursor:pointer;">Tengo un aval con propiedad en CDMX</button>
                        <button name="has_aval" value="0" class="btn btn-sm" style="border:1.5px solid #1D4ED8;background:#1D4ED8;color:#fff;border-radius:9px;padding:.55rem .9rem;font-weight:700;font-size:.8rem;cursor:pointer;">No tengo aval en CDMX</button>
                    </form>
                    <p style="font-size:.7rem;color:#94a3b8;margin:.4rem 0 0;">Sin aval en CDMX, la garantía es una póliza jurídica (Previsión Legal se encarga de la investigación).</p>
                @endif

                {{-- Garantía por póliza: DECIDE el propietario; el inquilino ve el plan, lo que le toca pagar y qué cubre --}}
                @if($step['key'] === 'garantia' && ! empty($step['decision']) && ! $slim)
                    @php $d = $step['decision']; $plan = $d['plan']->loadMissing('coverages'); @endphp
                    <div style="margin-top:.7rem;border:1.5px solid #1D4ED8;border-radius:14px;padding:.95rem 1rem;background:#fff;">
                        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;flex-wrap:wrap;">
                            <div style="font-weight:800;font-size:1rem;color:#0f172a;">Plan {{ $plan->name }}</div>
                            <div style="font-size:.72rem;color:#64748b;">{{ $plan->provider_name }} · {{ $d['decided_by'] === 'advisor' ? 'definido con tu asesor' : 'elegido por tu propietario' }}</div>
                        </div>
                        <div style="margin:.55rem 0;background:#eff6ff;border-radius:12px;padding:.7rem .85rem;">
                            <div style="font-size:.72rem;font-weight:700;color:#1e3a8a;text-transform:uppercase;letter-spacing:.05em;">Tu parte</div>
                            <div style="font-size:1.5rem;font-weight:800;color:#1D4ED8;line-height:1.15;">${{ number_format($d['split']['tenant']) }} MXN</div>
                            <div style="font-size:.76rem;color:#475569;margin-top:.15rem;">
                                @if($d['split']['tenant_pct'] === 100) La póliza (${{ number_format($d['amount']) }}) la cubres tú.
                                @else Se paga mitad y mitad: tu propietario cubre otros ${{ number_format($d['split']['owner']) }}. Total de la póliza: ${{ number_format($d['amount']) }}.
                                @endif
                            </div>
                        </div>
                        <ul style="margin:0 0 .6rem;padding:0;list-style:none;font-size:.78rem;color:#334155;line-height:1.5;">
                            <li>🧾 <strong>Anticipo por gastos de emisión:</strong> ${{ number_format($d['emission_fee']) }}. Se cubre al iniciar el trámite y <em>se acredita al precio</em> si se concreta; si no se concreta, no se reembolsa.</li>
                            <li>💳 @if($d['payment_mode'] === 'hdv') Tu asesor te indicará cómo pagar tu parte. @else Pagas tu parte <strong>directo a Previsión Legal</strong>; tu asesor te acompaña en el trámite. @endif</li>
                            @if($d['tenant_paid'])<li style="color:#166534;font-weight:700;">✅ Tu parte ya está pagada.</li>@endif
                        </ul>
                        <details style="font-size:.78rem;">
                            <summary style="cursor:pointer;color:#1D4ED8;font-weight:700;">Ver qué cubre este plan</summary>
                            <ul style="margin:.5rem 0 0;padding:0;list-style:none;line-height:1.5;color:#334155;">
                                @foreach($plan->includedCoverages() as $cov)
                                <li>✓ {{ $cov->label }}@if($cov->note)<span style="color:#94a3b8;"> — {{ $cov->note }}</span>@endif</li>
                                @endforeach
                            </ul>
                        </details>
                    </div>
                @endif

                {{-- Obligado solidario: el inquilino lo registra y captura SUS datos y documentos (el obligado no tiene cuenta). --}}
                @if($step['key'] === 'obligado' && ! $slim)
                    @php $os = $step['os_status']; $inp = 'width:100%;min-height:46px;font-size:16px;border:1px solid #cbd5e1;border-radius:10px;padding:0 .8rem;'; @endphp
                    @if($errors->any())<div style="margin-top:.6rem;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:.55rem .8rem;font-size:.78rem;">⚠ {{ $errors->first() }}</div>@endif
                    @if($os['registered'])
                        @php $bar = fn($p) => '<div style="height:7px;border-radius:9999px;background:#e2e8f0;overflow:hidden;"><div style="width:'.max(0,min(100,$p)).'%;height:100%;background:'.($p>=100?'#10b981':'#1D4ED8').';"></div></div>'; $docTot = array_sum($os['docs']); $docOk = $os['docs']['aprobado']; @endphp
                        <div style="margin-top:.7rem;border:1px solid #e2e8f0;border-radius:14px;padding:.85rem 1rem;background:#fff;">
                            <strong style="font-size:.9rem;">{{ $os['name'] }}</strong>
                            <div style="margin-top:.55rem;font-size:.74rem;color:#475569;">Sus datos · {{ $os['data_pct'] }}%</div>{!! $bar($os['data_pct']) !!}
                            <div style="margin-top:.5rem;font-size:.74rem;color:#475569;">Sus documentos aprobados · {{ $docOk }} de {{ $docTot }}</div>{!! $bar($docTot ? $docOk / $docTot * 100 : 0) !!}
                            <div style="display:grid;gap:.5rem;margin-top:.8rem;">
                                <a href="{{ route('portal.expediente', ['para' => 'obligado']) }}" style="display:flex;align-items:center;justify-content:center;min-height:46px;border-radius:12px;background:#1D4ED8;color:#fff;font-weight:800;font-size:.9rem;text-decoration:none;">📝 Llenar sus datos</a>
                                <a href="{{ route('portal.documents.index', ['para' => 'obligado']) }}" style="display:flex;align-items:center;justify-content:center;min-height:46px;border-radius:12px;border:1.5px solid #1D4ED8;background:#fff;color:#1D4ED8;font-weight:800;font-size:.9rem;text-decoration:none;">📄 Subir sus documentos</a>
                            </div>
                        </div>
                    @endif
                    @if(! $os['registered'] || ! $os['complete'])
                    <details style="margin-top:.6rem;" {{ $os['registered'] ? '' : 'open' }}>
                        <summary style="cursor:pointer;font-size:.8rem;font-weight:700;color:#1D4ED8;">{{ $os['registered'] ? '✏️ Corregir su nombre o celular' : 'Registrar a mi obligado solidario' }}</summary>
                        <form method="POST" action="{{ route('portal.rentals.obligado.store', $rental->id) }}" style="margin-top:.6rem;display:grid;gap:.6rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:.9rem;">
                            @csrf
                            <div><label style="font-size:.74rem;font-weight:700;color:#475569;">Nombre completo</label>
                                <input name="name" value="{{ old('name', $os['name']) }}" required autocomplete="off" style="{{ $inp }}"></div>
                            <div><label style="font-size:.74rem;font-weight:700;color:#475569;">Celular</label>
                                <input name="phone" type="tel" inputmode="tel" value="{{ old('phone', $os['client']?->phone) }}" required autocomplete="off" placeholder="10 dígitos" style="{{ $inp }}"></div>
                            <div><label style="font-size:.74rem;font-weight:700;color:#475569;">Correo <span style="font-weight:400;color:#94a3b8;">(opcional)</span></label>
                                <input name="email" type="email" inputmode="email" autocapitalize="none" value="{{ old('email', $os['client']?->email) }}" autocomplete="off" style="{{ $inp }}"></div>
                            <div><label style="font-size:.74rem;font-weight:700;color:#475569;">Relación contigo <span style="font-weight:400;color:#94a3b8;">(opcional)</span></label>
                                <input name="relationship" value="{{ old('relationship') }}" placeholder="Ej. mi mamá, mi socio" style="{{ $inp }}"></div>
                            <button style="min-height:50px;border:0;border-radius:12px;background:#1D4ED8;color:#fff;font-weight:800;font-size:1rem;cursor:pointer;">{{ $os['registered'] ? 'Guardar cambios' : 'Registrar' }}</button>
                            <p style="margin:0;font-size:.72rem;color:#94a3b8;line-height:1.45;">Tu obligado no necesita cuenta: tú capturas su información y subes sus documentos. Es confidencial: solo la ve tu asesor.</p>
                        </form>
                    </details>
                    @endif
                @endif

                {{-- Contrato disponible --}}
                @if($step['key'] === 'contrato' && ! empty($step['contract']) && $step['contract']->pdf_path)
                    <a href="{{ route('contracts.download', $step['contract']->id) }}" style="display:inline-block;margin-top:.5rem;font-size:.78rem;font-weight:700;color:#1D4ED8;">↓ Ver / descargar contrato (PDF)</a>
                @endif
            </div>
        </div>
    @endforeach
</div>
