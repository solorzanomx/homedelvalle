@extends('layouts.app-sidebar')
@section('title', 'Templates Transaccionales V4')

@section('content')
<div class="page-header">
    <div>
        <h2>Templates Transaccionales V4</h2>
        <p class="text-muted">Plantillas de email profesionales con diseño moderno</p>
    </div>
</div>

{{-- Info card --}}
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 2rem;">
    <div class="card-body">
        <h3 style="margin-top: 0; color: white;">🎨 Plantillas Modernas SaaS</h3>
        <p style="margin: 0.5rem 0 0; opacity: 0.95;">Diseño profesional con componentes reutilizables, responsive y compatible con todos los clientes de email.</p>
    </div>
</div>

{{-- Templates Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
    @forelse($v4Templates as $template)
        <div class="card" style="display: flex; flex-direction: column; transition: all 0.3s;">
            <div class="card-header" style="border-bottom: 2px solid #667eea;">
                <h3 style="margin: 0;">{{ $template['name'] }}</h3>
            </div>
            <div class="card-body" style="flex: 1;">
                <p style="color: #666; margin: 0 0 1rem;">{{ $template['description'] }}</p>

                <div style="display: flex; gap: 0.5rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid #eee;">
                    <a href="{{ route('admin.transactional-emails.preview', $template['id']) }}" class="btn btn-primary" style="flex: 1; text-align: center;">
                        👁️ Preview
                    </a>
                    <a href="{{ route('admin.transactional-emails.preview', $template['id']) }}" class="btn btn-outline" style="flex: 1; text-align: center;">
                        📧 Test
                    </a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">No hay templates disponibles.</p>
    @endforelse
</div>

<div class="card" style="margin-top: 2rem; background: #f9f9f9;">
    <div class="card-header"><h4>ℹ️ Información</h4></div>
    <div class="card-body">
        <ul style="margin: 0; padding-left: 1.5rem;">
            <li><strong>5 templates</strong> listos para usar</li>
            <li><strong>Diseño responsive</strong> compatible con móvil</li>
            <li><strong>Componentes reutilizables</strong> para mantener consistencia</li>
            <li><strong>Design tokens</strong> para colores y tipografía centralizados</li>
            <li><strong>Testing integrado</strong> para enviar emails de prueba</li>
        </ul>
    </div>
</div>

<style>
    .card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5568d3;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #667eea;
        color: #667eea;
    }

    .btn-outline:hover {
        background: #f0f0f7;
    }
</style>
@endsection
