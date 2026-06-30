@extends('layouts.app-sidebar')
@section('title', 'Acuses de Recibo')
@section('content')
<div class="page-header">
    <div>
        <h2>Acuses de Recibo</h2>
        <p class="text-muted">Correo de confirmación que le llega al lead al registrarse</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem;">
@foreach($types as $formType => $label)
@php $config = $configs->get($formType); @endphp
<div class="card" style="margin:0;display:flex;flex-direction:column;">
    <div class="card-body" style="flex:1;padding:1.1rem;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;margin-bottom:0.75rem;">
            <div>
                <p style="font-weight:700;font-size:0.9rem;margin:0 0 0.2rem;">{{ $label }}</p>
                <code style="font-size:0.72rem;color:var(--text-muted);background:var(--bg);padding:1px 6px;border-radius:4px;">{{ $formType }}</code>
            </div>
            @if($config)
                <span class="badge badge-green" style="flex-shrink:0;">Configurado</span>
            @else
                <span class="badge" style="flex-shrink:0;background:#fef3c7;color:#92400e;">Default</span>
            @endif
        </div>
        @if($config)
        <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.4;">{{ Str::limit($config->bajada, 80) }}</p>
        @endif
    </div>
    <div style="padding:0.6rem 1.1rem;border-top:1px solid var(--border);display:flex;gap:0.5rem;">
        <a href="{{ route('admin.acuse-configs.preview', $formType) }}" target="_blank" class="btn btn-outline btn-sm" style="flex:1;text-align:center;">Vista previa</a>
        <a href="{{ route('admin.acuse-configs.edit', $formType) }}" class="btn btn-primary btn-sm" style="flex:1;text-align:center;">Editar</a>
    </div>
</div>
@endforeach
</div>
@endsection
