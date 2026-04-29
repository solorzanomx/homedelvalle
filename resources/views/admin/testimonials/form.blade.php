@extends('layouts.app-sidebar')
@section('title', $testimonial ? 'Editar testimonio' : 'Nuevo testimonio')

@section('styles')
<style>
.preview-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border); }
.type-selector { display: flex; gap: 0.75rem; margin-bottom: 1rem; }
.type-opt { flex: 1; display: flex; align-items: center; gap: 0.4rem; padding: 0.55rem 0.85rem; border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: all 0.15s; font-size: 0.85rem; }
.type-opt:hover { border-color: var(--primary); }
.type-opt.active { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.type-opt input { display: none; }
.rating-selector { display: flex; gap: 0.25rem; }
.rating-star { font-size: 1.5rem; cursor: pointer; color: #d1d5db; transition: color 0.1s; }
.rating-star.active { color: #f59e0b; }
</style>
@endsection

@section('content')
<div class="page-header">
    <h2>{{ $testimonial ? 'Editar testimonio' : 'Nuevo testimonio' }}</h2>
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline">Volver</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ $testimonial ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}"
              enctype="multipart/form-data" id="testimonialForm">
            @csrf
            @if($testimonial) @method('PUT') @endif

            <div class="form-grid">
                {{-- Name --}}
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $testimonial->name ?? '') }}" required>
                    @error('name')<div class="form-hint" style="color:var(--danger);">{{ $message }}</div>@enderror
                </div>

                {{-- Role --}}
                <div class="form-group">
                    <label class="form-label">Rol / Titulo</label>
                    <input type="text" name="role" class="form-input" value="{{ old('role', $testimonial->role ?? '') }}" placeholder="Ej: Comprador en Del Valle">
                </div>

                {{-- Location --}}
                <div class="form-group">
                    <label class="form-label">Ubicacion</label>
                    <input type="text" name="location" class="form-input" value="{{ old('location', $testimonial->location ?? '') }}" placeholder="Ej: Del Valle, Narvarte">
                </div>

                {{-- Operation type --}}
                <div class="form-group">
                    <label class="form-label">Tipo de operación</label>
                    <select name="operation_type" class="form-select">
                        <option value="">— Sin especificar —</option>
                        @foreach(['Compra','Venta','Renta','Desarrollo','Inversión','Captación'] as $op)
                        <option value="{{ $op }}" {{ old('operation_type', $testimonial->operation_type ?? '') === $op ? 'selected' : '' }}>{{ $op }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Tipo de transacción del cliente.</div>
                </div>

                {{-- Ticket --}}
                <div class="form-group">
                    <label class="form-label">Ticket / Precio aproximado</label>
                    <input type="text" name="ticket" class="form-input" value="{{ old('ticket', $testimonial->ticket ?? '') }}" placeholder="Ej: $3.5 MDP · $8 MDP">
                    <div class="form-hint">Rango o valor aproximado de la operación.</div>
                </div>

                {{-- Time in market --}}
                <div class="form-group">
                    <label class="form-label">Tiempo en mercado</label>
                    <input type="text" name="time_in_market" class="form-input" value="{{ old('time_in_market', $testimonial->time_in_market ?? '') }}" placeholder="Ej: 32 días · 6 semanas">
                    <div class="form-hint">Cuánto tardó en cerrarse la operación.</div>
                </div>

                {{-- Sort order --}}
                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="sort_order" class="form-input" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}" min="0">
                </div>
            </div>

            {{-- Type selector --}}
            <div class="form-group">
                <label class="form-label">Tipo de testimonio</label>
                <div class="type-selector">
                    <label class="type-opt {{ old('type', $testimonial->type ?? 'text') === 'text' ? 'active' : '' }}" onclick="selectType('text')">
                        <input type="radio" name="type" value="text" {{ old('type', $testimonial->type ?? 'text') === 'text' ? 'checked' : '' }}>
                        <x-icon name="pen-line" class="w-4 h-4" /> Texto
                    </label>
                    <label class="type-opt {{ old('type', $testimonial->type ?? 'text') === 'video' ? 'active' : '' }}" onclick="selectType('video')">
                        <input type="radio" name="type" value="video" {{ old('type', $testimonial->type ?? 'text') === 'video' ? 'checked' : '' }}>
                        <x-icon name="circle-play" class="w-4 h-4" /> Video
                    </label>
                </div>
            </div>

            {{-- Content (always visible) --}}
            <div class="form-group">
                <label class="form-label">Testimonio (texto)</label>
                <textarea name="content" class="form-textarea" rows="4" placeholder="Escribe el testimonio del cliente...">{{ old('content', $testimonial->content ?? '') }}</textarea>
            </div>

            {{-- Video URL (only for video type) --}}
            <div class="form-group" id="videoField" style="display:none;">
                <label class="form-label">URL del video (YouTube)</label>
                <input type="url" name="video_url" class="form-input" value="{{ old('video_url', $testimonial->video_url ?? '') }}" placeholder="https://www.youtube.com/watch?v=...">
                <div class="form-hint">Pega el link de YouTube. Se convertira automaticamente a embed.</div>
            </div>

            {{-- Avatar --}}
            <div class="form-group">
                <label class="form-label">Foto del cliente</label>
                <div style="display:flex; align-items:center; gap:1rem;">
                    @if($testimonial && $testimonial->avatar)
                        <img src="{{ Storage::url($testimonial->avatar) }}" class="preview-avatar" id="avatarPreview">
                    @else
                        <div class="preview-avatar" id="avatarPreview" style="background:var(--bg); display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.75rem;">Sin foto</div>
                    @endif
                    <div>
                        <input type="file" name="avatar" accept="image/*" id="avatarInput" style="display:none;" onchange="previewFile(this)">
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('avatarInput').click()">Subir imagen</button>
                        <div class="form-hint">JPG, PNG. Max 2MB.</div>
                    </div>
                </div>
            </div>

            {{-- Rating --}}
            <div class="form-group">
                <label class="form-label">Calificacion</label>
                <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', $testimonial->rating ?? 5) }}">
                <div class="rating-selector" id="ratingSelector">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="rating-star {{ $i <= old('rating', $testimonial->rating ?? 5) ? 'active' : '' }}" data-val="{{ $i }}" onclick="setRating({{ $i }})">★</span>
                    @endfor
                </div>
            </div>

            {{-- Toggles --}}
            <div class="form-grid">
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $testimonial->is_active ?? true) ? 'checked' : '' }}>
                        Activo (visible en el sitio)
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $testimonial->is_featured ?? false) ? 'checked' : '' }}>
                        Destacado (video principal)
                    </label>
                    <div class="form-hint">Solo puede haber 1 destacado. Se desactivara el anterior.</div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $testimonial ? 'Guardar cambios' : 'Crear testimonio' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function selectType(type) {
    document.querySelectorAll('.type-opt').forEach(o => o.classList.remove('active'));
    document.querySelector('.type-opt input[value="'+type+'"]').checked = true;
    document.querySelector('.type-opt input[value="'+type+'"]').closest('.type-opt').classList.add('active');
    document.getElementById('videoField').style.display = type === 'video' ? 'block' : 'none';
}

function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.rating-star').forEach(s => {
        s.classList.toggle('active', parseInt(s.dataset.val) <= val);
    });
}

function previewFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('avatarPreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-avatar';
                img.id = 'avatarPreview';
                preview.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Init type visibility
selectType('{{ old('type', $testimonial->type ?? 'text') }}');
</script>
@endsection
