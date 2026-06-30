@extends('layouts.app-sidebar')
@section('title', 'Próximas Publicaciones')

@section('content')
<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:1px solid #d1d5db; background:#fff; text-decoration:none; color:#374151; transition:all .15s; }
.btn:hover { background:#f3f4f6; }
.btn-sm { padding:.35rem .7rem; font-size:.8rem; }
.card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
.upcoming-table { width:100%; border-collapse:collapse; }
.upcoming-table th { padding:.65rem 1rem; text-align:left; font-size:.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #f3f4f6; background:#f8fafc; }
.upcoming-table td { padding:.75rem 1rem; border-bottom:1px solid #f3f4f6; font-size:.875rem; vertical-align:middle; }
.upcoming-table tr:last-child td { border-bottom:none; }
.upcoming-table tr:hover td { background:#fafafa; }
.badge { display:inline-flex; align-items:center; gap:.3rem; padding:.2rem .6rem; border-radius:999px; font-size:.72rem; font-weight:700; }
.badge-blue   { background:#dbeafe; color:#1e40af; }
.badge-pink   { background:#fce7f3; color:#9d174d; }
.badge-green  { background:#d1fae5; color:#065f46; }
.badge-purple { background:#ede9fe; color:#5b21b6; }
.type-dot { width:7px; height:7px; border-radius:50%; }
</style>

<div class="page-header">
    <div>
        <h2 style="font-size:1.35rem;font-weight:800;color:#0C1A2E;margin:0;">&#128337; Próximas Publicaciones</h2>
        <p style="font-size:.83rem;color:#6b7280;margin:.25rem 0 0;">Contenido programado pendiente de publicación</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.social.calendar') }}" class="btn btn-sm">&#128197; Ver Calendario</a>
    </div>
</div>

<div class="card">
    @if($items->isEmpty())
    <div style="padding:3rem;text-align:center;color:#6b7280;">
        <p style="font-size:1.5rem;margin-bottom:.5rem;">&#128197;</p>
        <p style="font-weight:600;margin:0;">No hay contenido programado próximamente.</p>
        <p style="font-size:.85rem;margin-top:.25rem;">Programa posts, carruseles o historias desde sus respectivos editores.</p>
    </div>
    @else
    <table class="upcoming-table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Título</th>
                <th>Fecha programada</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <span class="badge badge-{{ $item['type_color'] }}">
                        <span class="type-dot" style="background:
                            @if($item['type_color']==='blue') #1d4ed8
                            @elseif($item['type_color']==='pink') #ec4899
                            @elseif($item['type_color']==='green') #10b981
                            @elseif($item['type_color']==='purple') #7c3aed
                            @else #6b7280 @endif
                        "></span>
                        {{ $item['type_label'] }}
                    </span>
                </td>
                <td style="font-weight:500;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $item['title'] }}
                </td>
                <td style="white-space:nowrap;">
                    @if($item['date'])
                    <span style="font-weight:600;">{{ \Carbon\Carbon::parse($item['date'])->isoFormat('ddd D MMM YYYY') }}</span>
                    <br>
                    <span style="font-size:.78rem;color:#6b7280;">{{ \Carbon\Carbon::parse($item['date'])->format('H:i') }}</span>
                    @else
                    <span style="color:#9ca3af;">—</span>
                    @endif
                </td>
                <td>
                    <span style="font-size:.75rem;font-weight:600;color:#6b7280;">{{ $item['status'] }}</span>
                </td>
                <td>
                    <a href="{{ $item['url'] }}" class="btn btn-sm">Ver →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
