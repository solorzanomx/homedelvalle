@extends('layouts.app-sidebar')
@section('title', 'Lead: ' . $submission->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">{{ $submission->full_name }}</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">
            {{ ucfirst($submission->form_type) }} · {{ $submission->created_at->format('d/m/Y H:i') }}
        </p>
    </div>
    <a href="{{ route('admin.form-submissions.index') }}" class="btn btn-outline">← Volver</a>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.85rem">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start">

    {{-- Main --}}
    <div>
        @php
            // Mensaje de WhatsApp contextual: si el lead preguntó por una
            // propiedad (portales), se menciona desde el primer mensaje —
            // responder con contexto gana la conversación.
            $propiedadLocal = null;
            if (!empty($submission->payload['propiedad_local_id'])) {
                $propiedadLocal = \App\Models\Property::find($submission->payload['propiedad_local_id']);
            }
            $nombreCorto = explode(' ', trim($submission->full_name))[0] ?: 'Hola';
            if ($propiedadLocal) {
                $waMsg = "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en «{$propiedadLocal->title}» (" . '$' . number_format((float) $propiedadLocal->price) . " {$propiedadLocal->currency}). Sigue disponible — ¿te gustaría agendar una visita esta semana?";
            } elseif (!empty($submission->payload['eb_titulo'])) {
                $waMsg = "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en «{$submission->payload['eb_titulo']}»"
                    . (!empty($submission->payload['eb_precio']) ? " ({$submission->payload['eb_precio']}" . (($submission->payload['eb_operacion'] ?? null) === 'renta' ? ' de renta' : '') . ')' : '')
                    . ". ¿Te gustaría agendar una visita esta semana?";
            } elseif (!empty($submission->payload['eb_property_id'])) {
                $waMsg = "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en la propiedad {$submission->payload['eb_property_id']} — con gusto te comparto los detalles. ¿Qué estás buscando: comprar o rentar?";
            } elseif (in_array($submission->form_type, ['vendedor', 'vendedor_predio'])) {
                $waMsg = "Hola {$nombreCorto}, soy de Home del Valle. Recibimos tu solicitud de valuación — ¿tienes 5 minutos para platicar de tu propiedad?";
            } else {
                $waMsg = "Hola {$nombreCorto}, te contactamos de Home del Valle sobre tu solicitud. ¿En qué horario te queda bien platicar?";
            }
            $esPosibleBroker = ($submission->lead_tag === 'LEAD_BROKER') || !empty($submission->payload['posible_broker']);

            // Si la IA ya redactó la respuesta, el WhatsApp sale con ella
            if (!empty($submission->payload['ai_respuesta'])) {
                $waMsg = $submission->payload['ai_respuesta'];
            }
        @endphp

        @if(!empty($submission->payload['ai_resumen']))
        <div class="card" style="border-left:4px solid #6366f1;background:#eef2ff">
            <div class="card-body" style="padding:0.85rem 1.2rem">
                <p style="margin:0;font-size:0.88rem;color:#3730a3"><strong>🤖 IA:</strong> {{ $submission->payload['ai_resumen'] }}
                    @if(!empty($submission->payload['ai_rol']))
                    <span class="badge" style="margin-left:0.5rem;background:#e0e7ff;color:#3730a3">{{ str_replace('_', ' ', $submission->payload['ai_rol']) }}</span>
                    @endif
                </p>
            </div>
        </div>
        @endif

        {{-- Respuesta sugerida por IA (tono de marca + pregunta calificadora) --}}
        <div class="card" style="border-left:4px solid #10b981;background:#ecfdf5">
            <div class="card-body" style="padding:0.95rem 1.2rem">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap">
                    <div style="flex:1;min-width:240px">
                        <p style="margin:0 0 0.35rem;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#047857">💬 Respuesta sugerida</p>
                        @if(!empty($submission->payload['ai_respuesta']))
                        <p style="margin:0;font-size:0.88rem;color:#064e3b;line-height:1.55;white-space:pre-line">{{ $submission->payload['ai_respuesta'] }}</p>
                        <p style="margin:0.5rem 0 0;font-size:0.72rem;color:#059669">Redactada por IA con el contexto del lead — revísala y ajústala antes de enviar. El botón de WhatsApp ya la lleva precargada.</p>
                        @else
                        <p style="margin:0;font-size:0.85rem;color:#065f46">Genera con IA el primer mensaje de WhatsApp: tono de la marca, datos del lead y una pregunta calificadora.</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.form-submissions.ai-suggest', $submission) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="border-color:#10b981;color:#047857;white-space:nowrap">
                            🤖 {{ !empty($submission->payload['ai_respuesta']) ? 'Regenerar' : 'Sugerir respuesta' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($esPosibleBroker)
        <div class="card" style="border-left:4px solid #86198f;background:#fdf4ff">
            <div class="card-body" style="display:flex;align-items:center;gap:1rem;justify-content:space-between;flex-wrap:wrap">
                <div>
                    <p style="font-weight:700;color:#86198f;margin:0">🤝 Posible broker pidiendo colaboración</p>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin:0.25rem 0 0">El mensaje sugiere que es un asesor, no un comprador. Regístralo en la red de colaboración para envíos de inventario compartido.</p>
                </div>
                <form method="POST" action="{{ route('admin.form-submissions.convert-broker', $submission) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="background:#86198f;border-color:#86198f">Guardar en Brokers Externos</button>
                </form>
            </div>
        </div>
        @endif

        @if($propiedadLocal || !empty($submission->payload['eb_property_id']))
        <div class="card">
            <div class="card-header"><h3>Propiedad de interés</h3></div>
            <div class="card-body" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
                @if($propiedadLocal)
                    @if($propiedadLocal->photo)
                    <img src="{{ asset('storage/' . $propiedadLocal->photo) }}" alt="" style="width:88px;height:66px;object-fit:cover;border-radius:8px">
                    @endif
                    <div style="flex:1;min-width:200px">
                        <p style="font-weight:700;margin:0">{{ $propiedadLocal->title }}</p>
                        <p style="font-size:0.85rem;color:var(--text-muted);margin:0.2rem 0 0">
                            ${{ number_format((float) $propiedadLocal->price) }} {{ $propiedadLocal->currency }}
                            · {{ $propiedadLocal->colony ?: $propiedadLocal->city }}
                            · {{ ucfirst($propiedadLocal->status) }}
                        </p>
                    </div>
                    <a href="{{ route('properties.show', $propiedadLocal) }}" class="btn btn-outline">Ver propiedad</a>
                @elseif(!empty($submission->payload['eb_titulo']))
                    <div style="flex:1;min-width:200px">
                        <p style="font-weight:700;margin:0">{{ $submission->payload['eb_titulo'] }}</p>
                        <p style="font-size:0.85rem;color:var(--text-muted);margin:0.2rem 0 0">
                            @if(!empty($submission->payload['eb_operacion']))
                            <span class="badge {{ $submission->payload['eb_operacion'] === 'venta' ? 'badge-blue' : 'badge-green' }}">{{ ucfirst($submission->payload['eb_operacion']) }}</span>
                            @endif
                            {{ $submission->payload['eb_precio'] ?? '' }}
                            @if(!empty($submission->payload['eb_ubicacion'])) · {{ $submission->payload['eb_ubicacion'] }} @endif
                            <span style="color:var(--text-muted)">· solo en EasyBroker</span>
                        </p>
                    </div>
                    <a href="{{ $submission->payload['eb_url'] ?? 'https://www.easybroker.com/agent/properties?search=' . urlencode($submission->payload['eb_property_id'] ?? '') }}" target="_blank" rel="noopener" class="btn btn-outline">Ver en EasyBroker ↗</a>
                @else
                    <div style="flex:1">
                        <p style="font-weight:600;margin:0">{{ $submission->payload['eb_property_id'] }} <span style="font-weight:400;color:var(--text-muted)">(solo en EasyBroker — no está en el sitio)</span></p>
                    </div>
                    <a href="https://www.easybroker.com/agent/properties?search={{ urlencode($submission->payload['eb_property_id']) }}" target="_blank" rel="noopener" class="btn btn-outline">Buscar en EasyBroker ↗</a>
                @endif
            </div>
        </div>
        @endif

        {{-- Contact info --}}
        <div class="card">
            <div class="card-header"><h3>Datos de contacto</h3></div>
            <div class="card-body" style="padding:0">
                @foreach([
                    ['Email',    $submission->email,   'mailto:'.$submission->email],
                    ['Teléfono', $submission->phone,   'https://wa.me/'.preg_replace('/[^0-9]/','',$submission->phone).'?text='.urlencode($waMsg)],
                    ['Fuente',   $submission->source_page,  null],
                    ['IP',       $submission->ip,      null],
                    ['UTM',      collect(['utm_source'=>$submission->utm_source,'utm_medium'=>$submission->utm_medium,'utm_campaign'=>$submission->utm_campaign])->filter()->map(fn($v,$k)=>"{$k}={$v}")->implode(' · ') ?: '—', null],
                ] as [$label, $value, $href])
                <div style="padding:0.85rem 1.2rem;border-bottom:1px solid var(--border);display:flex;gap:1rem;align-items:center">
                    <span style="font-size:0.75rem;font-weight:600;color:var(--text-muted);width:80px;flex-shrink:0">{{ $label }}</span>
                    @if($href)
                    <a href="{{ $href }}" target="_blank" style="font-size:0.88rem;color:var(--primary)">{{ $value ?: '—' }}</a>
                    @else
                    <span style="font-size:0.88rem">{{ $value ?: '—' }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payload --}}
        <div class="card">
            <div class="card-header"><h3>Datos del formulario</h3></div>
            <div class="card-body" style="padding:0">
                @forelse($submission->payload ?? [] as $key => $value)
                <div style="padding:0.75rem 1.2rem;border-bottom:1px solid var(--border);display:flex;gap:1rem;align-items:flex-start">
                    <span style="font-size:0.75rem;font-weight:600;color:var(--text-muted);width:140px;flex-shrink:0;text-transform:capitalize">{{ str_replace('_', ' ', $key) }}</span>
                    <span style="font-size:0.85rem;word-break:break-word">
                        @if(is_array($value))
                            {{ implode(', ', $value) }}
                        @else
                            {{ $value ?: '—' }}
                        @endif
                    </span>
                </div>
                @empty
                <div style="padding:1.2rem;color:var(--text-muted);font-size:0.85rem">Sin datos adicionales</div>
                @endforelse
            </div>
        </div>

        {{-- Notes --}}
        <div class="card">
            <div class="card-header"><h3>Notas internas</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.form-submissions.notes', $submission) }}">
                    @csrf @method('PATCH')
                    <textarea name="notes" class="form-textarea" rows="4" placeholder="Escribe tus notas de seguimiento...">{{ $submission->notes }}</textarea>
                    <button type="submit" class="btn btn-primary" style="margin-top:0.75rem">Guardar notas</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        {{-- Temperatura --}}
        @php
            $tempMeta = match($submission->lead_temperature) {
                'hot'  => ['label'=>'🔥 Caliente', 'bg'=>'#fef2f2', 'color'=>'#b91c1c', 'border'=>'#fca5a5', 'desc'=>'Lead muy interesado, responder hoy'],
                'warm' => ['label'=>'☀ Templado',  'bg'=>'#fffbeb', 'color'=>'#b45309', 'border'=>'#fcd34d', 'desc'=>'Lead interesado, seguimiento pronto'],
                'cold' => ['label'=>'❄ Frío',      'bg'=>'#eff6ff', 'color'=>'#1d4ed8', 'border'=>'#93c5fd', 'desc'=>'Lead en exploración'],
                default=> ['label'=>'Sin temperatura', 'bg'=>'#f8fafc', 'color'=>'var(--text-muted)', 'border'=>'var(--border)', 'desc'=>''],
            };
        @endphp
        <div style="background:{{ $tempMeta['bg'] }};border:1px solid {{ $tempMeta['border'] }};border-radius:var(--radius);padding:1rem 1.1rem;margin-bottom:1rem">
            <div style="font-size:0.7rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:{{ $tempMeta['color'] }};opacity:0.75;margin-bottom:0.35rem">Temperatura del lead</div>
            <div style="font-size:1.1rem;font-weight:700;color:{{ $tempMeta['color'] }}">{{ $tempMeta['label'] }}</div>
            @if($tempMeta['desc'])
            <div style="font-size:0.75rem;color:{{ $tempMeta['color'] }};opacity:0.8;margin-top:0.2rem">{{ $tempMeta['desc'] }}</div>
            @endif
        </div>

        {{-- Convertir a cliente --}}
        @if(!$submission->client_id)
        <form method="POST" action="{{ route('admin.form-submissions.convert-client', $submission) }}" style="margin-bottom:1rem"
              onsubmit="return confirm('¿Convertir a «{{ $submission->full_name }}» en cliente?')">
            @csrf
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:#1d4ed8;border-color:#1d4ed8">
                ✦ Convertir a cliente
            </button>
        </form>
        @else
        <div style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem">
            <svg style="width:16px;height:16px;color:#059669;flex-shrink:0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span style="font-size:0.82rem;font-weight:600;color:#065f46">Ya convertido a cliente <span style="font-weight:400">(ID #{{ $submission->client_id }})</span></span>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><h3>Estado del lead</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.form-submissions.status', $submission) }}">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">Estado actual</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            @foreach(['new'=>'Nuevo','contacted'=>'Contactado','qualified'=>'Calificado','won'=>'Ganado','lost'=>'Perdido'] as $v=>$l)
                            <option value="{{ $v }}" {{ $submission->status === $v ? 'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
                    @foreach([
                        ['Tag',       $submission->lead_tag],
                        ['Registrado',$submission->created_at->format('d/m/Y H:i')],
                        ['Contactado',$submission->contacted_at?->format('d/m/Y H:i') ?? '—'],
                    ] as [$label,$value])
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;padding:0.35rem 0">
                        <span style="color:var(--text-muted)">{{ $label }}</span>
                        <span style="font-weight:500">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>

                @if($submission->phone && $submission->phone !== 'sin teléfono')
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$submission->phone) }}?text={{ urlencode($waMsg) }}"
                   target="_blank" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1rem;background:#25D366;border-color:#25D366">
                    💬 Responder por WhatsApp
                </a>
                <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.4rem;line-height:1.4">Mensaje pre-armado con la propiedad de interés — edítalo en WhatsApp antes de enviar si hace falta.</p>
                @endif

                @if(!$esPosibleBroker && $submission->form_type === 'easybroker')
                <form method="POST" action="{{ route('admin.form-submissions.convert-broker', $submission) }}" style="margin-top:0.5rem">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center;font-size:0.8rem"
                            onclick="return confirm('¿Registrar a {{ $submission->full_name }} como broker externo (red de colaboración)?')">
                        🤝 Es un broker — guardar en red de colaboración
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
