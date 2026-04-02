@extends('layouts.app-sidebar')
@section('title', 'Etiquetas del Blog')

@section('styles')
<style>
    .tags-layout { display: grid; grid-template-columns: 380px 1fr; gap: 1.5rem; align-items: start; }
    @media (max-width: 1024px) { .tags-layout { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Etiquetas del Blog</h2>
        <p class="text-muted">{{ $tags->count() }} etiquetas</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">&#8592; Blog Posts</a>
</div>

<div class="tags-layout">
    {{-- Create form --}}
    <div class="card">
        <div class="card-header"><h3>Nueva Etiqueta</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tags.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" required placeholder="Nombre de la etiqueta">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-input" value="{{ old('slug') }}" placeholder="Se genera automaticamente">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Crear Etiqueta</button>
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
                    @forelse($tags as $tag)
                    <tr>
                        <td>{{ $tag->name }}</td>
                        <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $tag->slug }}</td>
                        <td>{{ $tag->posts_count }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;"
                                        onclick="editTag({{ $tag->id }}, '{{ addslashes($tag->name) }}', '{{ $tag->slug }}')">
                                    Editar
                                </button>
                                <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" onsubmit="return confirm('Eliminar esta etiqueta?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; color: var(--danger);">&#10005;</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay etiquetas.</td>
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
        <h3 style="margin-bottom: 1rem;">Editar Etiqueta</h3>
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
function editTag(id, name, slug) {
    document.getElementById('editForm').action = '{{ url("admin/tags") }}/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editSlug').value = slug;
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
