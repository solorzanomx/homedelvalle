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
