@extends('layouts.app-sidebar')
@section('title', 'Templates de Email')

@section('content')
<div class="page-header">
    <div>
        <h2>Templates de Email</h2>
        <p class="text-muted">Administra las plantillas de correo del sistema</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.email.settings') }}" class="btn btn-outline">&#9881; Config SMTP</a>
        <a href="{{ route('admin.email.templates.create') }}" class="btn btn-primary">+ Nuevo Template</a>
    </div>
</div>

{{-- Variables reference --}}
<div class="card">
    <div class="card-header"><h3>Variables Disponibles</h3></div>
    <div class="card-body" style="padding:0.75rem 1.5rem;">
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
            @php
                $variables = [
                    'Nombre' => 'Nombre del usuario',
                    'Apellido' => 'Apellido del usuario',
                    'Email' => 'Correo electronico',
                    'Password' => 'Contrasena asignada',
                    'Fecha' => 'Fecha actual',
                    'Rol' => 'Rol del usuario',
                    'Sitio' => 'Nombre del sitio',
                ];
            @endphp
            @foreach($variables as $key => $label)
                <code style="background:var(--bg); padding:0.2rem 0.6rem; border-radius:4px; font-size:0.8rem; border:1px solid var(--border);" title="@{{ {{ $key }} }}">{{ $label }} <span style="opacity:0.5; font-size:0.72rem;">@{{ {{ $key }} }}</span></code>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Asunto</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td>
                            <div style="font-weight:500;">{{ $template->name }}</div>
                        </td>
                        <td>{{ $template->subject }}</td>
                        <td class="text-muted">{{ $template->updated_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.email.templates.preview', $template) }}" class="btn btn-sm btn-outline" target="_blank" title="Vista previa">&#9673;</a>
                                <a href="{{ route('admin.email.templates.edit', $template) }}" class="btn btn-sm btn-outline">Editar</a>
                                <form method="POST" action="{{ route('admin.email.templates.destroy', $template) }}" style="display:inline" onsubmit="return confirm('Eliminar template \'{{ $template->name }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding:2rem;">
                            No hay templates creados.
                            <a href="{{ route('admin.email.templates.create') }}" style="color:var(--primary); text-decoration:underline;">Crear el primero</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
