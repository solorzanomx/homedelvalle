{{-- "¿Qué sigue?" del inquilino. Espera: $rental (RentalProcess donde el cliente es el inquilino). Fuente: App\Support\TenantRoadmap --}}
@php
    $rm = \App\Support\TenantRoadmap::build($rental);
    $icons = ['apartado' => '🔑', 'documentos' => '📄', 'garantia' => '🛡️', 'contrato' => '✍️', 'entrega' => '🏠'];
@endphp
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1.1rem 1.25rem;margin-bottom:1.25rem;">
    <div style="display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.25rem;">
        <h3 style="margin:0;font-size:1rem;color:#0f172a;">Tu camino a la renta</h3>
        <span style="font-size:.72rem;color:#64748b;">{{ collect($rm['steps'])->where('state','done')->count() }} de {{ count($rm['steps']) }} pasos completos</span>
    </div>
    <p style="margin:0 0 .9rem;font-size:.78rem;color:#64748b;">Siempre sabrás en qué paso vas y qué sigue.</p>

    @foreach($rm['steps'] as $i => $step)
        @php
            $st = $step['state'];
            $dot = ['done' => ['#10b981', '#ecfdf5'], 'active' => ['#1D4ED8', '#eff6ff'], 'pending' => ['#f59e0b', '#fffbeb'], 'todo' => ['#cbd5e1', '#f8fafc']][$st];
        @endphp
        <div style="display:flex;gap:.8rem;{{ $loop->last ? '' : 'padding-bottom:1rem;' }}">
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
                <div style="font-size:.8rem;color:{{ $st === 'todo' ? '#94a3b8' : '#475569' }};margin-top:.15rem;line-height:1.45;">{{ $step['summary'] }}</div>

                {{-- Documentos: enlace a corregir/subir --}}
                @if($step['key'] === 'documentos' && $st === 'active')
                    <a href="{{ route('portal.expediente') }}" style="display:inline-block;margin-top:.5rem;font-size:.78rem;font-weight:700;color:#1D4ED8;">Ir a mis documentos →</a>
                @endif

                {{-- Garantía: ¿tienes aval en CDMX? --}}
                @if($step['key'] === 'garantia' && ($step['action'] ?? null) === 'declare')
                    <form method="POST" action="{{ route('portal.rentals.guarantee.declare', $rental->id) }}" style="margin-top:.6rem;display:flex;gap:.5rem;flex-wrap:wrap;">
                        @csrf
                        <button name="has_aval" value="1" class="btn btn-sm" style="border:1.5px solid #1D4ED8;background:#fff;color:#1D4ED8;border-radius:9px;padding:.55rem .9rem;font-weight:700;font-size:.8rem;cursor:pointer;">Tengo un aval con propiedad en CDMX</button>
                        <button name="has_aval" value="0" class="btn btn-sm" style="border:1.5px solid #1D4ED8;background:#1D4ED8;color:#fff;border-radius:9px;padding:.55rem .9rem;font-weight:700;font-size:.8rem;cursor:pointer;">No tengo aval en CDMX</button>
                    </form>
                    <p style="font-size:.7rem;color:#94a3b8;margin:.4rem 0 0;">Sin aval en CDMX, la garantía es una póliza jurídica (Previsión Legal se encarga de la investigación).</p>
                @endif

                {{-- Garantía por póliza: elegir plan --}}
                @if($step['key'] === 'garantia' && ($step['action'] ?? null) === 'choose_plan')
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.7rem;margin-top:.7rem;">
                        @foreach($rm['plans'] as $plan)
                        <div style="border:{{ $plan->is_recommended ? '2px solid #1D4ED8' : '1px solid #e2e8f0' }};border-radius:12px;padding:.85rem;background:#fff;position:relative;display:flex;flex-direction:column;">
                            @if($plan->tagline)<span style="position:absolute;top:-.6rem;left:.8rem;background:#1D4ED8;color:#fff;font-size:.62rem;font-weight:700;padding:.12rem .55rem;border-radius:9999px;">{{ $plan->tagline }}</span>@endif
                            <div style="font-weight:700;font-size:.92rem;color:#0f172a;">{{ $plan->name }}</div>
                            <div style="font-size:1.25rem;font-weight:800;color:#1D4ED8;margin:.15rem 0 .4rem;">{{ $plan->price_formatted }}</div>
                            @if($plan->description)<div style="font-size:.74rem;color:#64748b;margin-bottom:.4rem;">{{ $plan->description }}</div>@endif
                            <ul style="margin:0 0 .7rem;padding:0;list-style:none;font-size:.75rem;color:#334155;line-height:1.5;flex:1;">
                                @foreach(($plan->inclusions ?? []) as $inc)<li>✓ {{ $inc }}</li>@endforeach
                            </ul>
                            <form method="POST" action="{{ route('portal.rentals.poliza.select', $rental->id) }}" onsubmit="return confirm('¿Elegir el plan {{ $plan->name }} ({{ $plan->price_formatted }})?')">
                                @csrf <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button class="btn btn-sm btn-primary" style="width:100%;border-radius:9px;padding:.55rem;font-weight:700;font-size:.8rem;cursor:pointer;">Elegir {{ $plan->name }}</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    <p style="font-size:.7rem;color:#94a3b8;margin:.5rem 0 0;">El pago de la póliza lo realizas directamente con {{ $rm['plans']->first()->provider_name ?? 'el proveedor' }}. Tu asesor te acompaña en el trámite.</p>
                @endif

                {{-- Garantía con plan elegido: cambiar plan mientras no esté aprobada --}}
                @if($step['key'] === 'garantia' && isset($step['plan']) && ! $step['done'] && $rm['plans']->count() > 1)
                    <details style="margin-top:.5rem;font-size:.75rem;"><summary style="cursor:pointer;color:#1D4ED8;font-weight:600;">Cambiar de plan</summary>
                        <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.4rem;">
                            @foreach($rm['plans']->where('id', '!=', $step['plan']->id) as $plan)
                            <form method="POST" action="{{ route('portal.rentals.poliza.select', $rental->id) }}" onsubmit="return confirm('¿Cambiar al plan {{ $plan->name }} ({{ $plan->price_formatted }})?')">
                                @csrf <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button class="btn btn-sm btn-outline" style="border-radius:8px;padding:.35rem .7rem;font-size:.75rem;cursor:pointer;">{{ $plan->name }} — {{ $plan->price_formatted }}</button>
                            </form>
                            @endforeach
                        </div>
                    </details>
                @endif

                {{-- Contrato disponible --}}
                @if($step['key'] === 'contrato' && ! empty($step['contract']) && $step['contract']->pdf_path)
                    <a href="{{ route('contracts.download', $step['contract']->id) }}" style="display:inline-block;margin-top:.5rem;font-size:.78rem;font-weight:700;color:#1D4ED8;">↓ Ver / descargar contrato (PDF)</a>
                @endif
            </div>
        </div>
    @endforeach
</div>
