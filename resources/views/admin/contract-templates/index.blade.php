@extends('layouts.app-sidebar')
@section('title', 'Plantillas de Contrato')

@section('content')
<div class="page-header">
    <div>
        <h2>Plantillas de Contrato</h2>
        <p class="text-muted">Plantillas para generar contratos de arrendamiento</p>
    </div>
    <a href="{{ route('admin.contract-templates.create') }}" class="btn btn-primary">+ Nueva Plantilla</a>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Contratos</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td style="font-weight:500;">{{ $template->name }}</td>
                        <td>
                            <span class="badge badge-blue">{{ $template->type_label }}</span>
                        </td>
                        <td>
                            @if($template->is_active)
                                <span class="badge badge-green">Activa</span>
                            @else
                                <span class="badge badge-yellow">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $template->contracts()->count() }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $template->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.contract-templates.preview', $template) }}" class="btn btn-sm btn-outline" target="_blank">Vista Previa</a>
                                <a href="{{ route('admin.contract-templates.edit', $template) }}" class="btn btn-sm btn-outline">Editar</a>
                                <form method="POST" action="{{ route('admin.contract-templates.destroy', $template) }}" style="display:inline" onsubmit="return confirm('Eliminar esta plantilla?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:2rem;">No hay plantillas. <a href="{{ route('admin.contract-templates.create') }}">Crear primera plantilla</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Variables Reference --}}
<div class="card">
    <div class="card-header">
        <h3>Variables Disponibles</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Variable</th><th>Descripcion</th></tr></thead>
                <tbody>
                    @foreach(\App\Models\ContractTemplate::DEFAULT_VARIABLES as $var => $desc)
                    <tr>
                        <td><code style="background:var(--bg); padding:0.15rem 0.4rem; border-radius:4px; font-size:0.82rem;">{{ $var }}</code></td>
                        <td class="text-muted">{{ $desc }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
