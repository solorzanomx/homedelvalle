@extends('layouts.app-sidebar')
@section('title', 'Gestionar Centro de Ayuda')

@section('styles')
<style>
.manage-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem; }
.manage-header h2 { font-size: 1.3rem; font-weight: 700; }

/* Tabs */
.manage-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--border); margin-bottom: 1.5rem; }
.manage-tab {
    padding: 0.6rem 1.2rem; font-size: 0.88rem; font-weight: 600; color: var(--text-muted);
    cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s;
    background: none; border-top: none; border-left: none; border-right: none;
}
.manage-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.manage-tab:hover { color: var(--text); }

.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* Table */
.manage-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.manage-table th { text-align: left; padding: 0.6rem 0.75rem; font-weight: 600; color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.03em; border-bottom: 2px solid var(--border); }
.manage-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--border); vertical-align: top; }
.manage-table tr:hover td { background: var(--hover); }

/* Badges */
.badge-cat { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; background: var(--primary-light, #e8e0ff); color: var(--primary); }
.badge-pub { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
.badge-pub.pub { background: #d4edda; color: #155724; }
.badge-pub.draft { background: #fff3cd; color: #856404; }
.badge-type { display: inline-block; padding: 0.15rem 0.55rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
.badge-type.tip { background: #d1ecf1; color: #0c5460; }
.badge-type.warning { background: #fff3cd; color: #856404; }
.badge-type.pro_tip { background: #d4edda; color: #155724; }

/* Stats row */
.stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; text-align: center; }
.stat-num { font-size: 1.6rem; font-weight: 800; color: var(--primary); }
.stat-label { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; }

/* Form modal */
.modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
.modal-backdrop.show { display: flex; }
.modal-box { background: var(--card); border-radius: 12px; padding: 1.5rem; width: 95%; max-width: 640px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; }
.modal-box label { display: block; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.25rem; margin-top: 0.75rem; }
.modal-box input, .modal-box select, .modal-box textarea { width: 100%; padding: 0.5rem 0.65rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.85rem; background: var(--bg); }
.modal-box textarea { min-height: 200px; font-family: monospace; font-size: 0.82rem; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.25rem; }

/* Buttons */
.btn-sm { padding: 0.3rem 0.7rem; font-size: 0.78rem; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; }
.btn-primary { background: var(--primary); color: #fff; }
.btn-primary:hover { opacity: 0.9; }
.btn-secondary { background: var(--border); color: var(--text); }
.btn-danger { background: #dc3545; color: #fff; }
.btn-danger:hover { background: #c82333; }
.btn-ghost { background: none; color: var(--text-muted); padding: 0.3rem 0.5rem; }
.btn-ghost:hover { color: var(--text); }

/* Category cards */
.cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
.cat-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 1rem; text-align: center; }
.cat-icon { font-size: 1.8rem; margin-bottom: 0.35rem; }
.cat-name { font-weight: 700; font-size: 0.9rem; }
.cat-count { font-size: 0.78rem; color: var(--text-muted); }

.truncate-text { max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Actions */
.action-btns { display: flex; gap: 0.35rem; }
</style>
@endsection

@section('content')
<div class="manage-header">
    <h2>Gestionar Centro de Ayuda</h2>
    <div style="display:flex;gap:0.5rem;">
        <a href="{{ route('help.index') }}" class="btn-sm btn-secondary">Ver Help Center</a>
    </div>
</div>

@if(session('success'))
<div style="background:#d4edda;color:#155724;padding:0.65rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.85rem;">
    {{ session('success') }}
</div>
@endif

<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-num">{{ $categories->count() }}</div>
        <div class="stat-label">Categorias</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">{{ $articles->count() }}</div>
        <div class="stat-label">Articulos</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">{{ $articles->where('is_published', true)->count() }}</div>
        <div class="stat-label">Publicados</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">{{ $tips->count() }}</div>
        <div class="stat-label">Tips contextuales</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">{{ $articles->sum('view_count') }}</div>
        <div class="stat-label">Vistas totales</div>
    </div>
</div>

<!-- Tabs -->
<div class="manage-tabs">
    <button class="manage-tab active" onclick="switchTab('categories')">Categorias</button>
    <button class="manage-tab" onclick="switchTab('articles')">Articulos</button>
    <button class="manage-tab" onclick="switchTab('tips')">Tips Contextuales</button>
</div>

<!-- ═══════ TAB: Categorias ═══════ -->
<div id="tab-categories" class="tab-panel active">
    <div class="cat-grid">
        @foreach($categories as $cat)
        <div class="cat-card">
            <div class="cat-icon">{{ $cat->icon }}</div>
            <div class="cat-name">{{ $cat->name }}</div>
            <div class="cat-count">{{ $cat->articles_count }} articulos</div>
        </div>
        @endforeach
    </div>
</div>

<!-- ═══════ TAB: Articulos ═══════ -->
<div id="tab-articles" class="tab-panel">
    <div style="margin-bottom:1rem;">
        <button class="btn-sm btn-primary" onclick="openArticleModal()">+ Nuevo Articulo</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="manage-table">
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Categoria</th>
                <th>Estado</th>
                <th>Vistas</th>
                <th>Orden</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($articles as $article)
            <tr>
                <td style="font-weight:600;">{{ $article->title }}</td>
                <td><span class="badge-cat">{{ $article->category->icon ?? '' }} {{ $article->category->name ?? '-' }}</span></td>
                <td>
                    <span class="badge-pub {{ $article->is_published ? 'pub' : 'draft' }}">
                        {{ $article->is_published ? 'Publicado' : 'Borrador' }}
                    </span>
                </td>
                <td>{{ number_format($article->view_count) }}</td>
                <td>{{ $article->sort_order }}</td>
                <td>
                    <div class="action-btns">
                        <a href="{{ route('help.article', $article->slug) }}" class="btn-sm btn-ghost" title="Ver">👁</a>
                        <button class="btn-sm btn-ghost" title="Editar"
                            onclick="openEditArticleModal({{ json_encode([
                                'id' => $article->id,
                                'title' => $article->title,
                                'help_category_id' => $article->help_category_id,
                                'content' => $article->content,
                                'sort_order' => $article->sort_order,
                                'is_published' => $article->is_published,
                            ]) }})">&#9998;</button>
                        <form method="POST" action="{{ route('admin.help.articles.destroy', $article) }}" onsubmit="return confirm('Eliminar este articulo?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm btn-ghost" title="Eliminar" style="color:#dc3545;">&#128465;</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<!-- ═══════ TAB: Tips ═══════ -->
<div id="tab-tips" class="tab-panel">
    <div style="margin-bottom:1rem;">
        <button class="btn-sm btn-primary" onclick="openTipModal()">+ Nuevo Tip</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="manage-table">
        <thead>
            <tr>
                <th>Contexto</th>
                <th>Titulo</th>
                <th>Tipo</th>
                <th>Contenido</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tips as $tip)
            <tr>
                <td><code style="font-size:0.78rem;background:var(--hover);padding:0.15rem 0.4rem;border-radius:4px;">{{ $tip->context }}</code></td>
                <td style="font-weight:600;">{{ $tip->title }}</td>
                <td><span class="badge-type {{ $tip->type }}">{{ $tip->type }}</span></td>
                <td><div class="truncate-text">{{ $tip->content }}</div></td>
                <td>
                    <form method="POST" action="{{ route('admin.help.tips.destroy', $tip) }}" onsubmit="return confirm('Eliminar este tip?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-sm btn-ghost" title="Eliminar" style="color:#dc3545;">&#128465;</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<!-- ═══════ MODAL: Nuevo/Editar Articulo ═══════ -->
<div class="modal-backdrop" id="articleModal">
    <div class="modal-box">
        <div class="modal-title" id="articleModalTitle">Nuevo Articulo</div>
        <form method="POST" id="articleForm">
            @csrf
            <div id="articleMethodField"></div>

            <label for="art_category">Categoria</label>
            <select name="help_category_id" id="art_category" required>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                @endforeach
            </select>

            <label for="art_title">Titulo</label>
            <input type="text" name="title" id="art_title" required placeholder="Titulo del articulo">

            <label for="art_content">Contenido (Markdown)</label>
            <textarea name="content" id="art_content" required placeholder="# Titulo&#10;&#10;Escribe el contenido en Markdown..."></textarea>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div>
                    <label for="art_sort">Orden</label>
                    <input type="number" name="sort_order" id="art_sort" value="0" min="0">
                </div>
                <div>
                    <label style="margin-top:0.75rem;">
                        <input type="checkbox" name="is_published" id="art_published" value="1" checked>
                        Publicado
                    </label>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-sm btn-secondary" onclick="closeModal('articleModal')">Cancelar</button>
                <button type="submit" class="btn-sm btn-primary" id="articleSubmitBtn">Crear</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════ MODAL: Nuevo Tip ═══════ -->
<div class="modal-backdrop" id="tipModal">
    <div class="modal-box">
        <div class="modal-title">Nuevo Tip Contextual</div>
        <form method="POST" action="{{ route('admin.help.tips.store') }}">
            @csrf

            <label for="tip_context">Contexto (route key)</label>
            <input type="text" name="context" id="tip_context" required placeholder="ej: clients.create, properties.index" maxlength="80">

            <label for="tip_title">Titulo</label>
            <input type="text" name="title" id="tip_title" required placeholder="Titulo corto del tip">

            <label for="tip_content">Contenido</label>
            <textarea name="content" id="tip_content" required style="min-height:100px;" placeholder="Texto del consejo..."></textarea>

            <label for="tip_type">Tipo</label>
            <select name="type" id="tip_type">
                <option value="tip">Tip</option>
                <option value="pro_tip">Pro Tip</option>
                <option value="warning">Advertencia</option>
            </select>

            <div class="modal-actions">
                <button type="button" class="btn-sm btn-secondary" onclick="closeModal('tipModal')">Cancelar</button>
                <button type="submit" class="btn-sm btn-primary">Crear Tip</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(name) {
    document.querySelectorAll('.manage-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
}

function openArticleModal() {
    document.getElementById('articleModalTitle').textContent = 'Nuevo Articulo';
    document.getElementById('articleForm').action = '{{ route("admin.help.articles.store") }}';
    document.getElementById('articleMethodField').innerHTML = '';
    document.getElementById('art_title').value = '';
    document.getElementById('art_content').value = '';
    document.getElementById('art_sort').value = '0';
    document.getElementById('art_published').checked = true;
    document.getElementById('articleSubmitBtn').textContent = 'Crear';
    document.getElementById('articleModal').classList.add('show');
}

function openEditArticleModal(data) {
    document.getElementById('articleModalTitle').textContent = 'Editar Articulo';
    document.getElementById('articleForm').action = '/admin/help/articles/' + data.id;
    document.getElementById('articleMethodField').innerHTML = '@method("PUT")';
    document.getElementById('art_title').value = data.title;
    document.getElementById('art_category').value = data.help_category_id;
    document.getElementById('art_content').value = data.content;
    document.getElementById('art_sort').value = data.sort_order;
    document.getElementById('art_published').checked = data.is_published;
    document.getElementById('articleSubmitBtn').textContent = 'Guardar';
    document.getElementById('articleModal').classList.add('show');
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function openTipModal() { document.getElementById('tipModal').classList.add('show'); }

// Close modals on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('show'); });
});
</script>
@endsection
