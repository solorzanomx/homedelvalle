@extends('layouts.app-sidebar')
@section('title', 'Formularios')

@section('content')
<div class="page-header">
    <div>
        <h2>Formularios</h2>
        <p class="text-muted">{{ $forms->count() }} formularios</p>
    </div>
    <a href="{{ route('admin.forms.create') }}" class="btn btn-primary">+ Nuevo Formulario</a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Campos</th>
                    <th>Envios</th>
                    <th>Estado</th>
                    <th style="width: 200px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($forms as $form)
                <tr>
                    <td style="font-weight: 500;">{{ $form->name }}</td>
                    <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $form->slug }}</td>
                    <td>{{ count($form->fields ?? []) }}</td>
                    <td>{{ $form->submissions_count }}</td>
                    <td>
                        <span class="badge {{ $form->is_active ? 'badge-success' : 'badge-muted' }}">
                            {{ $form->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Editar</a>
                            <a href="{{ route('admin.forms.submissions', $form) }}" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Envios</a>
                            <form method="POST" action="{{ route('admin.forms.destroy', $form) }}" onsubmit="return confirm('Eliminar este formulario?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; color: var(--danger);">&#10005;</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay formularios. Crea tu primer formulario.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
