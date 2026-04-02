@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $menu->name)

@section('styles')
<style>
    .menu-builder { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; }
    .menu-item-card { border: 1px solid var(--border); border-radius: 6px; margin-bottom: 0.5rem; background: var(--card); }
    .menu-item-header { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 0.75rem; cursor: grab; }
    .menu-item-header:active { cursor: grabbing; }
    .menu-item-body { padding: 0.75rem; border-top: 1px solid var(--border); }
    .menu-children { margin-left: 1.5rem; margin-top: 0.25rem; }
    .add-item-card { border: 1px dashed var(--border); border-radius: 6px; padding: 0.75rem; margin-bottom: 0.5rem; cursor: pointer; text-align: center; color: var(--text-muted); transition: all 0.15s; }
    .add-item-card:hover { border-color: var(--primary); color: var(--primary); }
    @media (max-width: 1024px) { .menu-builder { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $menu->name }}</h2>
        <p class="text-muted">{{ $menu->location === 'header' ? 'Navegacion principal del sitio' : 'Links en el footer' }}</p>
    </div>
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

<div x-data="menuBuilder()" class="menu-builder">
    {{-- Left: Add items --}}
    <div>
        {{-- Pages --}}
        <div class="card">
            <div class="card-header"><h3>Paginas</h3></div>
            <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                @forelse($pages as $p)
                <div class="add-item-card" @click="addItem({label: '{{ addslashes($p->title) }}', type: 'page', page_id: {{ $p->id }}, url: '/p/{{ $p->slug }}'})">
                    {{ $p->title }}
                </div>
                @empty
                <p class="text-muted" style="font-size: 0.82rem;">No hay paginas publicadas.</p>
                @endforelse
            </div>
        </div>

        {{-- Routes --}}
        <div class="card">
            <div class="card-header"><h3>Rutas del sitio</h3></div>
            <div class="card-body">
                @php $routes = [
                    ['label' => 'Inicio', 'route' => 'home'],
                    ['label' => 'Propiedades', 'route' => 'propiedades.index'],
                    ['label' => 'Blog', 'route' => 'blog.index'],
                    ['label' => 'Nosotros', 'route' => 'nosotros'],
                    ['label' => 'Contacto', 'route' => 'contacto'],
                ]; @endphp
                @foreach($routes as $r)
                <div class="add-item-card" @click="addItem({label: '{{ $r['label'] }}', type: 'route', route_name: '{{ $r['route'] }}'})">
                    {{ $r['label'] }}
                </div>
                @endforeach
            </div>
        </div>

        {{-- Custom URL --}}
        <div class="card">
            <div class="card-header"><h3>URL personalizada</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <input type="text" class="form-input" x-model="customLabel" placeholder="Texto del enlace">
                </div>
                <div class="form-group">
                    <input type="text" class="form-input" x-model="customUrl" placeholder="https://...">
                </div>
                <button type="button" class="btn btn-outline" style="width: 100%;" @click="addCustom()">Agregar</button>
            </div>
        </div>
    </div>

    {{-- Right: Menu structure --}}
    <div>
        <form method="POST" action="{{ route('admin.menus.update-items', $menu) }}">
            @csrf
            <input type="hidden" name="items_json" :value="JSON.stringify(items)">

            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Estructura del menu</h3>
                    <span class="text-muted" style="font-size: 0.78rem;" x-text="items.length + ' items'"></span>
                </div>
                <div class="card-body">
                    <template x-if="items.length === 0">
                        <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            <p>Menu vacio. Agrega items desde el panel izquierdo.</p>
                        </div>
                    </template>

                    <template x-for="(item, index) in items" :key="index">
                        <div class="menu-item-card">
                            <div class="menu-item-header">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="color: var(--text-muted);">&#9776;</span>
                                    <span style="font-size: 0.85rem; font-weight: 500;" x-text="item.label"></span>
                                    <span style="font-size: 0.7rem; color: var(--text-muted); background: var(--bg); padding: 0.1rem 0.4rem; border-radius: 4px;" x-text="item.type"></span>
                                </div>
                                <div style="display: flex; gap: 0.3rem;">
                                    <button type="button" @click="moveItemUp(index)" :disabled="index === 0" style="padding: 0.15rem 0.35rem; font-size: 0.72rem; border: 1px solid var(--border); border-radius: 3px; background: transparent; cursor: pointer;">&uarr;</button>
                                    <button type="button" @click="moveItemDown(index)" :disabled="index === items.length - 1" style="padding: 0.15rem 0.35rem; font-size: 0.72rem; border: 1px solid var(--border); border-radius: 3px; background: transparent; cursor: pointer;">&darr;</button>
                                    <button type="button" @click="toggleEdit(index)" style="padding: 0.15rem 0.35rem; font-size: 0.72rem; border: 1px solid var(--border); border-radius: 3px; background: transparent; cursor: pointer;">&#9998;</button>
                                    <button type="button" @click="removeItem(index)" style="padding: 0.15rem 0.35rem; font-size: 0.72rem; border: 1px solid var(--danger); border-radius: 3px; background: transparent; color: var(--danger); cursor: pointer;">&times;</button>
                                </div>
                            </div>

                            <div x-show="item._edit" class="menu-item-body">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                    <div class="form-group"><label class="form-label" style="font-size:0.72rem;">Label</label><input type="text" class="form-input" x-model="item.label"></div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size:0.72rem;">Target</label>
                                        <select class="form-select" x-model="item.target">
                                            <option value="_self">Misma ventana</option>
                                            <option value="_blank">Nueva ventana</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size:0.72rem;">Estilo</label>
                                        <select class="form-select" x-model="item.style">
                                            <option value="link">Link</option>
                                            <option value="button">Boton</option>
                                            <option value="muted">Muted</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="font-size:0.72rem;">Activo</label>
                                        <select class="form-select" x-model="item.is_active">
                                            <option :value="true">Si</option>
                                            <option :value="false">No</option>
                                        </select>
                                    </div>
                                </div>
                                <template x-if="item.type === 'url'">
                                    <div class="form-group"><label class="form-label" style="font-size:0.72rem;">URL</label><input type="text" class="form-input" x-model="item.url"></div>
                                </template>
                            </div>

                            {{-- Children --}}
                            <template x-if="item.children && item.children.length">
                                <div class="menu-children">
                                    <template x-for="(child, ci) in item.children" :key="ci">
                                        <div style="border: 1px solid var(--border); border-radius: 4px; margin-bottom: 0.3rem; padding: 0.4rem 0.6rem; display: flex; justify-content: space-between; align-items: center; background: var(--bg);">
                                            <span style="font-size: 0.82rem;" x-text="child.label"></span>
                                            <button type="button" @click="item.children.splice(ci, 1)" style="padding: 0.1rem 0.3rem; font-size: 0.7rem; color: var(--danger); border: none; background: transparent; cursor: pointer;">&times;</button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="form-actions" style="border: none; padding-top: 0.75rem;">
                <a href="{{ route('admin.menus.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Menu</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function menuBuilder() {
    var existingItems = @json($items->map(fn($i) => [
        'label' => $i->label,
        'type' => $i->type,
        'page_id' => $i->page_id,
        'url' => $i->url,
        'route_name' => $i->route_name,
        'target' => $i->target,
        'style' => $i->style,
        'is_active' => $i->is_active,
        'parent_id' => $i->parent_id,
        'children' => [],
    ]));

    // Build tree from flat list
    var rootItems = existingItems.filter(function(i) { return !i.parent_id; });
    // Note: for simplicity, children are stored flat in DB and we rebuild simple nesting here
    // Deep nesting is not supported in this version

    return {
        items: rootItems,
        customLabel: '',
        customUrl: '',
        addItem: function(data) {
            this.items.push({
                label: data.label || 'Item',
                type: data.type || 'url',
                page_id: data.page_id || null,
                url: data.url || '',
                route_name: data.route_name || '',
                target: '_self',
                style: 'link',
                is_active: true,
                children: [],
                _edit: false
            });
        },
        addCustom: function() {
            if (!this.customLabel || !this.customUrl) return;
            this.addItem({ label: this.customLabel, type: 'url', url: this.customUrl });
            this.customLabel = '';
            this.customUrl = '';
        },
        removeItem: function(index) {
            if (confirm('Eliminar este item?')) this.items.splice(index, 1);
        },
        toggleEdit: function(index) {
            this.items[index]._edit = !this.items[index]._edit;
        },
        moveItemUp: function(index) {
            if (index > 0) { var tmp = this.items[index]; this.items[index] = this.items[index - 1]; this.items[index - 1] = tmp; }
        },
        moveItemDown: function(index) {
            if (index < this.items.length - 1) { var tmp = this.items[index]; this.items[index] = this.items[index + 1]; this.items[index + 1] = tmp; }
        }
    };
}
</script>
@endsection
