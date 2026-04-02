@extends('layouts.app-sidebar')
@section('title', 'Envios: ' . $form->name)

@section('content')
<div class="page-header">
    <div>
        <h2>Envios: {{ $form->name }}</h2>
        <p class="text-muted">{{ $submissions->total() }} envios</p>
    </div>
    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    @foreach($form->fields as $field)
                        @if(!in_array($field['type'] ?? '', ['hidden']))
                        <th>{{ $field['label'] ?: $field['name'] }}</th>
                        @endif
                    @endforeach
                    <th>Fecha</th>
                    <th>Fuente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $sub)
                <tr style="{{ !$sub->is_read ? 'font-weight: 600;' : '' }}">
                    <td><span style="display: inline-block; width: 8px; height: 8px; border-radius: 50; background: {{ $sub->is_read ? 'var(--border)' : 'var(--primary)' }};"></span></td>
                    @foreach($form->fields as $field)
                        @if(!in_array($field['type'] ?? '', ['hidden']))
                        <td style="font-size: 0.82rem;">{{ Str::limit($sub->data[$field['name']] ?? '-', 50) }}</td>
                        @endif
                    @endforeach
                    <td style="font-size: 0.78rem; color: var(--text-muted);">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td style="font-size: 0.78rem;">{{ $sub->utm_source ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count(array_filter($form->fields, fn($f) => ($f['type'] ?? '') !== 'hidden')) + 3 }}" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay envios.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($submissions->hasPages())
<div style="margin-top: 1rem; display: flex; justify-content: center;">
    {{ $submissions->links() }}
</div>
@endif
@endsection
