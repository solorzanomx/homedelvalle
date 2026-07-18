@extends('layouts.app-sidebar')
@section('title', 'Campañas de Blog')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Campañas de Blog</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Brief → mapa de temas → borradores con IA → tu OK → publicación automática</p>
    </div>
    <a href="{{ route('admin.blog-campaigns.create') }}" class="btn btn-primary">+ Nueva campaña</a>
</div>

@if(session('success'))<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.85rem">{{ session('success') }}</div>@endif

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Campaña</th><th>Estado</th><th>Cadencia</th><th>Temas</th><th>Posts</th><th></th></tr></thead>
            <tbody>
                @forelse($campaigns as $c)
                @php $t = collect($c->topics ?? []); @endphp
                <tr>
                    <td style="font-weight:600">{{ $c->name }}</td>
                    <td><span class="badge {{ ['active'=>'badge-green','draft'=>'badge-yellow','paused'=>'','done'=>'badge-blue'][$c->status] ?? '' }}">{{ ['active'=>'Activa','draft'=>'Borrador','paused'=>'Pausada','done'=>'Terminada'][$c->status] ?? $c->status }}</span></td>
                    <td>{{ $c->posts_per_week }}/semana</td>
                    <td>{{ $t->where('status','pending')->count() }} pendientes · {{ $t->where('status','generated')->count() }} generados</td>
                    <td>{{ $c->posts_count }}</td>
                    <td><a href="{{ route('admin.blog-campaigns.show', $c) }}" class="btn btn-outline" style="padding:0.3rem 0.8rem;font-size:0.8rem">Abrir</a></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted)">Sin campañas aún — crea la de lanzamiento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
