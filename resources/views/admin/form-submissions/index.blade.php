@extends('layouts.app-sidebar')
@section('title', 'Leads & Formularios')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Leads & Formularios</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Todas las solicitudes recibidas desde el sitio</p>
    </div>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.85rem">{{ session('success') }}</div>
@endif

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:1rem;margin-bottom:1.5rem">
    @foreach([
        ['label'=>'Total',     'val'=>$counts['total'],     'color'=>'#6366f1'],
        ['label'=>'Nuevos',    'val'=>$counts['new'],       'color'=>'#f59e0b'],
        ['label'=>'Vendedor',  'val'=>$counts['vendedor'],  'color'=>'#3b82f6'],
        ['label'=>'Comprador', 'val'=>$counts['comprador'], 'color'=>'#10b981'],
        ['label'=>'B2B',       'val'=>$counts['b2b'],       'color'=>'#8b5cf6'],
        ['label'=>'Contacto',  'val'=>$counts['contacto'],  'color'=>'#64748b'],
    ] as $s)
    <div class="card" style="margin:0;padding:1rem;text-align:center">
        <p style="font-size:1.6rem;font-weight:800;color:{{ $s['color'] }};margin:0">{{ $s['val'] }}</p>
        <p style="font-size:0.75rem;color:var(--text-muted);margin:0.2rem 0 0">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.form-submissions.index') }}" style="display:flex;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap">
    <input type="text" name="search" placeholder="Buscar nombre, email, teléfono..." value="{{ request('search') }}" class="form-input" style="flex:1;min-width:200px">
    <select name="type" onchange="this.form.submit()" class="form-select" style="width:auto">
        <option value="">Todos los tipos</option>
        <option value="vendedor"  {{ request('type')==='vendedor'  ? 'selected':'' }}>Vendedor</option>
        <option value="comprador" {{ request('type')==='comprador' ? 'selected':'' }}>Comprador</option>
        <option value="b2b"       {{ request('type')==='b2b'       ? 'selected':'' }}>B2B</option>
        <option value="contacto"  {{ request('type')==='contacto'  ? 'selected':'' }}>Contacto</option>
    </select>
    <select name="status" onchange="this.form.submit()" class="form-select" style="width:auto">
        <option value="">Todos los estados</option>
        <option value="new"       {{ request('status')==='new'       ? 'selected':'' }}>Nuevo</option>
        <option value="contacted" {{ request('status')==='contacted' ? 'selected':'' }}>Contactado</option>
        <option value="qualified" {{ request('status')==='qualified' ? 'selected':'' }}>Calificado</option>
        <option value="won"       {{ request('status')==='won'       ? 'selected':'' }}>Ganado</option>
        <option value="lost"      {{ request('status')==='lost'      ? 'selected':'' }}>Perdido</option>
    </select>
    @if(request('search')||request('type')||request('status'))
    <a href="{{ route('admin.form-submissions.index') }}" class="btn btn-outline">Limpiar</a>
    @endif
</form>

<div class="card">
    @if($submissions->count() > 0)
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Email / WhatsApp</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $sub)
                @php
                    $typeColors = ['vendedor'=>'badge-blue','comprador'=>'badge-green','b2b'=>'badge-yellow','contacto'=>''];
                    $statusColors = ['new'=>'badge-yellow','contacted'=>'badge-blue','qualified'=>'badge-green','won'=>'badge-green','lost'=>'badge-red'];
                    $statusLabels = ['new'=>'Nuevo','contacted'=>'Contactado','qualified'=>'Calificado','won'=>'Ganado','lost'=>'Perdido'];
                @endphp
                <tr>
                    <td style="font-weight:600">{{ $sub->full_name }}</td>
                    <td><span class="badge {{ $typeColors[$sub->form_type] ?? '' }}">{{ ucfirst($sub->form_type) }}</span></td>
                    <td><span class="badge {{ $statusColors[$sub->status] ?? '' }}">{{ $statusLabels[$sub->status] ?? $sub->status }}</span></td>
                    <td style="font-size:0.82rem">
                        <div>{{ $sub->email }}</div>
                        <div style="color:var(--text-muted)">{{ $sub->phone }}</div>
                    </td>
                    <td style="color:var(--text-muted);font-size:0.82rem;white-space:nowrap">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.form-submissions.show', $sub) }}" class="btn btn-outline btn-sm">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:0.75rem 1.2rem;border-top:1px solid var(--border)">
        {{ $submissions->links() }}
    </div>
    @else
    <div style="padding:3rem;text-align:center;color:var(--text-muted)">
        <p style="margin:0">
            @if(request('search')||request('type')||request('status'))
                No hay leads con esos filtros.
            @else
                Aún no hay envíos. Aparecerán aquí cuando alguien llene un formulario.
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
