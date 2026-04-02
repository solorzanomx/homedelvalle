@extends('layouts.app-sidebar')
@section('title', 'Canales de Marketing')

@section('styles')
<style>
/* Nav pills */
.mkt-pills { display: flex; gap: 0.4rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.mkt-pill {
    padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.mkt-pill:hover { border-color: var(--primary); color: var(--text); }
.mkt-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Add form */
.ch-add {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1rem 1.25rem; margin-bottom: 1.25rem;
}
.ch-add-grid { display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap; }
.ch-add-grid .form-group { margin: 0; }

/* Channel list */
.ch-list { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.ch-item {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem;
    border-bottom: 1px solid var(--border); transition: background 0.1s;
}
.ch-item:last-child { border-bottom: none; }
.ch-item:hover { background: rgba(248,250,252,0.8); }
.ch-color { width: 28px; height: 28px; border-radius: 6px; flex-shrink: 0; border: 1px solid rgba(0,0,0,0.08); }
.ch-info { flex: 1; min-width: 0; }
.ch-name { font-size: 0.88rem; font-weight: 600; }
.ch-meta { font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 0.5rem; }
.ch-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.ch-leads { font-size: 0.82rem; font-weight: 500; padding: 0.2rem 0.5rem; background: var(--bg); border-radius: 4px; }

/* Edit row */
.ch-edit {
    display: none; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    background: var(--bg);
}
.ch-edit.open { display: block; }
.ch-edit-fields { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

/* Empty */
.ch-empty { text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.85rem; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Canales de Marketing</h2>
        <p class="text-muted">{{ $channels->count() }} canal{{ $channels->count() !== 1 ? 'es' : '' }}</p>
    </div>
</div>

{{-- Nav pills --}}
<div class="mkt-pills">
    <a href="{{ route('admin.marketing.dashboard') }}" class="mkt-pill">Resumen</a>
    <a href="{{ route('admin.marketing.campaigns') }}" class="mkt-pill">Campanas</a>
    <a href="{{ route('admin.marketing.channels') }}" class="mkt-pill active">Canales</a>
</div>

{{-- Add channel form --}}
<div class="ch-add">
    <form method="POST" action="{{ route('admin.marketing.channels.store') }}">
        @csrf
        <div class="ch-add-grid">
            <div class="form-group" style="flex:1; min-width:160px;">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-input" required placeholder="Google Ads, Facebook...">
            </div>
            <div class="form-group" style="width:140px;">
                <label class="form-label">Tipo</label>
                <select name="type" class="form-select" required>
                    @foreach($types as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="width:60px;">
                <label class="form-label">Color</label>
                <input type="color" name="color" value="#3b82f6" style="width:100%; height:38px; padding:2px; border:1px solid var(--border); border-radius:var(--radius); cursor:pointer;">
            </div>
            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.4rem; cursor:pointer; padding:0.55rem 0;">
                    <input type="checkbox" name="is_active" value="1" checked style="accent-color:var(--primary);"> Activo
                </label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Agregar</button>
            </div>
        </div>
    </form>
</div>

{{-- Channel list --}}
<div class="ch-list">
    @forelse($channels as $channel)
    <div class="ch-item" id="view-{{ $channel->id }}">
        <div class="ch-color" style="background:{{ $channel->color }};"></div>
        <div class="ch-info">
            <div class="ch-name">{{ $channel->name }}</div>
            <div class="ch-meta">
                <span>{{ $types[$channel->type] ?? $channel->type }}</span>
                @if(!$channel->is_active)<span>&middot; <span style="color:#ef4444;">Inactivo</span></span>@endif
            </div>
        </div>
        <div class="ch-right">
            <div class="ch-leads">{{ $channel->clients_count }} leads</div>
            @if($channel->is_active)
                <span class="badge badge-green" style="font-size:0.68rem;">Activo</span>
            @else
                <span class="badge badge-red" style="font-size:0.68rem;">Inactivo</span>
            @endif
            <button type="button" class="btn btn-sm btn-outline" style="padding:0.2rem 0.5rem; font-size:0.72rem;" onclick="toggleEdit({{ $channel->id }})">&#9998;</button>
            <form method="POST" action="{{ route('admin.marketing.channels.destroy', $channel) }}" style="display:inline" onsubmit="return confirm('Eliminar este canal?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem; font-size:0.72rem;">&#10005;</button>
            </form>
        </div>
    </div>
    <div class="ch-edit" id="edit-{{ $channel->id }}">
        <form method="POST" action="{{ route('admin.marketing.channels.update', $channel) }}">
            @csrf @method('PUT')
            <div class="ch-edit-fields">
                <input type="text" name="name" class="form-input" value="{{ $channel->name }}" required style="max-width:200px;">
                <select name="type" class="form-select" required style="max-width:140px;">
                    @foreach($types as $val => $label)
                        <option value="{{ $val }}" {{ $channel->type === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="color" name="color" value="{{ $channel->color }}" style="width:40px; height:34px; padding:2px; border:1px solid var(--border); border-radius:var(--radius); cursor:pointer;">
                <label style="display:flex; align-items:center; gap:0.3rem; cursor:pointer; font-size:0.82rem;">
                    <input type="checkbox" name="is_active" value="1" {{ $channel->is_active ? 'checked' : '' }} style="accent-color:var(--primary);"> Activo
                </label>
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit({{ $channel->id }})">Cancelar</button>
            </div>
        </form>
    </div>
    @empty
    <div class="ch-empty">No hay canales. Crea el primero arriba.</div>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
function toggleEdit(id) {
    var view = document.getElementById('view-' + id);
    var edit = document.getElementById('edit-' + id);
    var isOpen = edit.classList.contains('open');
    edit.classList.toggle('open');
    view.style.display = isOpen ? '' : 'none';
}
</script>
@endsection
