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
        {{-- Contact info --}}
        <div class="card">
            <div class="card-header"><h3>Datos de contacto</h3></div>
            <div class="card-body" style="padding:0">
                @foreach([
                    ['Email',    $submission->email,   'mailto:'.$submission->email],
                    ['Teléfono', $submission->phone,   'https://wa.me/'.preg_replace('/[^0-9]/','',$submission->phone).'?text='.urlencode('Hola '.$submission->full_name.', te contactamos de Home del Valle.')],
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

                @if($submission->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$submission->phone) }}?text={{ urlencode('Hola '.$submission->full_name.', te contactamos de Home del Valle sobre tu solicitud.') }}"
                   target="_blank" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1rem;background:#25D366;border-color:#25D366">
                    Abrir WhatsApp
                </a>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
