@extends('layouts.app-sidebar')
@section('title', 'Nueva Historia')

@section('content')
<style>
.form-wrap { max-width:640px; margin:0 auto; }
.card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; margin-bottom:1.25rem; }
.card-header { padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; font-weight:700; font-size:.92rem; display:flex; align-items:center; gap:.4rem; }
.card-body { padding:1.25rem; display:flex; flex-direction:column; gap:1rem; }
.form-label { display:block; font-size:.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.03em; margin-bottom:.35rem; }
.form-input, .form-select, .form-textarea { width:100%; padding:.55rem .75rem; border:1px solid #d1d5db; border-radius:8px; font-size:.88rem; background:#fff; box-sizing:border-box; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline:none; border-color:#1d4ed8; box-shadow:0 0 0 2px rgba(29,78,216,.12); }
.form-hint { font-size:.75rem; color:#9ca3af; margin-top:.25rem; }
.char-counter { font-size:.72rem; color:#9ca3af; text-align:right; margin-top:.2rem; }
.char-counter.warn { color:#f59e0b; }
.char-counter.limit { color:#dc2626; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:1px solid transparent; text-decoration:none; transition:all .15s; }
.btn-primary { background:#1d4ed8; color:#fff; }
.btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
.btn-outline:hover { background:#f3f4f6; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media(max-width:480px) { .grid-2 { grid-template-columns:1fr; } }
</style>

<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
    <a href="{{ route('admin.social.stories.index') }}" class="btn btn-outline" style="padding:.35rem .7rem;font-size:.8rem;">&#8592; Historias</a>
    <h2 style="font-size:1.35rem;font-weight:800;color:#0C1A2E;margin:0;">Nueva Historia</h2>
</div>

<div class="form-wrap">
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#dc2626;">
        <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.social.stories.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header">&#127775; Configuración de la Historia</div>
            <div class="card-body">
                <div class="grid-2">
                    <div>
                        <label class="form-label">Plataforma</label>
                        <select name="platform" class="form-select" required>
                            <option value="instagram" {{ old('platform','instagram')==='instagram' ? 'selected':'' }}>Instagram</option>
                            <option value="facebook"  {{ old('platform')==='facebook' ? 'selected':'' }}>Facebook</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipo de Media</label>
                        <select name="media_type" class="form-select">
                            <option value="image" {{ old('media_type','image')==='image' ? 'selected':'' }}>Imagen</option>
                            <option value="video" {{ old('media_type')==='video' ? 'selected':'' }}>Video (próximamente)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Headline <span style="color:#9ca3af;font-weight:400;">(texto principal de la historia)</span></label>
                    <input type="text" name="headline" class="form-input" maxlength="100"
                           value="{{ old('headline') }}" placeholder="Ej: Nuevo departamento en Narvarte"
                           oninput="updateCounter(this, 'headlineCount', 100)">
                    <div class="char-counter" id="headlineCount">0 / 100</div>
                </div>

                <div>
                    <label class="form-label">Hashtags del sticker <span style="color:#9ca3af;font-weight:400;">(separados por coma)</span></label>
                    <input type="text" name="sticker_hashtags" class="form-input"
                           value="{{ old('sticker_hashtags') }}"
                           placeholder="Ej: homedelvalle, narvarte, departamento">
                    <p class="form-hint">Sin el símbolo #, se añade automáticamente.</p>
                </div>

                <div class="grid-2">
                    <div>
                        <label class="form-label">Sticker de Ubicación</label>
                        <input type="text" name="sticker_location" class="form-input" maxlength="100"
                               value="{{ old('sticker_location') }}" placeholder="Ej: Narvarte, CDMX">
                    </div>
                    <div>
                        <label class="form-label">Sticker de Link</label>
                        <input type="url" name="sticker_link" class="form-input" maxlength="255"
                               value="{{ old('sticker_link') }}" placeholder="https://...">
                    </div>
                </div>

                <div>
                    <label class="form-label">Programar publicación <span style="color:#9ca3af;font-weight:400;">(opcional)</span></label>
                    <input type="datetime-local" name="scheduled_at" class="form-input"
                           value="{{ old('scheduled_at') }}"
                           min="{{ now()->format('Y-m-d\TH:i') }}">
                    <p class="form-hint">Si se establece, el status será "Programada" y se publicará automáticamente.</p>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
            <a href="{{ route('admin.social.stories.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear Historia &rarr;</button>
        </div>
    </form>
</div>

<script>
function updateCounter(el, counterId, max) {
    const len = el.value.length;
    const counter = document.getElementById(counterId);
    counter.textContent = len + ' / ' + max;
    counter.className = 'char-counter' + (len >= max ? ' limit' : (len >= max * 0.85 ? ' warn' : ''));
}
// Init counters
document.querySelectorAll('[oninput^="updateCounter"]').forEach(el => {
    const match = el.getAttribute('oninput').match(/updateCounter\(this, '(\w+)', (\d+)\)/);
    if (match) updateCounter(el, match[1], parseInt(match[2]));
});
</script>
@endsection
