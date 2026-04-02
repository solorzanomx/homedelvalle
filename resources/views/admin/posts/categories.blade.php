@extends('layouts.app-sidebar')
@section('title', 'Categorias del Blog')

@section('styles')
<style>
    .categories-grid { display: grid; grid-template-columns: 380px 1fr; gap: 1.5rem; align-items: start; }
    @media (max-width: 1024px) { .categories-grid { grid-template-columns: 1fr; } }
    .color-options { display: flex; gap: 0.4rem; flex-wrap: wrap; }
    .color-option { width: 26px; height: 26px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.15s; }
    .color-option:hover, .color-option.selected { border-color: var(--text); transform: scale(1.15); }
    .color-option input { display: none; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Categorias del Blog</h2>
        <p class="text-muted">{{ $categories->count() }} categorias</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">&#8592; Blog Posts</a>
</div>

<div class="categories-grid">
    {{-- Create form --}}
    <div class="card">
        <div class="card-header"><h3>Nueva Categoria</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.post-categories.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Nombre de la categoria">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-input" value="{{ old('slug') }}" placeholder="Se genera automaticamente">
                </div>
                <div class="form-group">
                    <label class="form-label">Descripcion</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="Descripcion breve (opcional)">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <div class="color-options">
                        @php $colors = ['blue', 'green', 'red', 'purple', 'orange', 'pink', 'teal', 'yellow']; @endphp
                        @foreach($colors as $color)
                        <label class="color-option" style="background: var(--color-{{ $color }}, {{ $color }});" title="{{ $color }}">
                            <input type="radio" name="color" value="{{ $color }}" {{ old('color') === $color ? 'checked' : '' }}>
                        </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Crear Categoria</button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Posts</th>
                        <th style="width: 160px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>
                            @if($cat->color)
                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $cat->color }}; margin-right: 0.4rem;"></span>
                            @endif
                            {{ $cat->name }}
                        </td>
                        <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $cat->slug }}</td>
                        <td>{{ $cat->posts_count }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;"
                                        onclick="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->slug }}', '{{ addslashes($cat->description ?? '') }}', '{{ $cat->color ?? '' }}')">
                                    Editar
                                </button>
                                <form method="POST" action="{{ route('admin.post-categories.destroy', $cat) }}" onsubmit="return confirm('Eliminar esta categoria?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; color: var(--danger);">&#10005;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay categorias.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div id="editModal" style="display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div style="background: var(--card); border-radius: 12px; padding: 1.5rem; width: 420px; max-width: 95vw;">
        <h3 style="margin-bottom: 1rem;">Editar Categoria</h3>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Nombre <span class="required">*</span></label>
                <input type="text" name="name" id="editName" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" id="editSlug" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Descripcion</label>
                <textarea name="description" id="editDescription" class="form-textarea" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <div class="color-options" id="editColors">
                    @foreach($colors as $color)
                    <label class="color-option" style="background: var(--color-{{ $color }}, {{ $color }});" title="{{ $color }}">
                        <input type="radio" name="color" value="{{ $color }}">
                    </label>
                    @endforeach
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editCategory(id, name, slug, description, color) {
    document.getElementById('editForm').action = '{{ url("admin/post-categories") }}/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editSlug').value = slug;
    document.getElementById('editDescription').value = description;
    document.querySelectorAll('#editColors input').forEach(function(r) { r.checked = (r.value === color); });
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
@endsection
