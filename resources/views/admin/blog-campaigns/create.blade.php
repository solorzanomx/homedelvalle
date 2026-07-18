@extends('layouts.app-sidebar')
@section('title', 'Nueva campaña de blog')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Nueva campaña</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Define el brief; la IA genera el mapa de temas para tu aprobación.</p>
    </div>
    <a href="{{ route('admin.blog-campaigns.index') }}" class="btn btn-outline">← Volver</a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.blog-campaigns.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="name" class="form-input" required placeholder="Ej: Lanzamiento julio-agosto" value="{{ old('name') }}">
                @error('name')<p style="color:#dc2626;font-size:0.8rem">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Objetivo de la campaña</label>
                <textarea name="objetivo" class="form-textarea" rows="3" placeholder="Ej: Campaña de lanzamiento — construir autoridad temática en Benito Juárez y detectar qué temas generan leads. Prioridad al funnel de predios.">{{ old('objetivo') }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">Posts por semana *</label>
                    <select name="posts_per_week" class="form-select">
                        <option value="7" {{ old('posts_per_week')==7?'selected':'' }}>7 (diario)</option>
                        <option value="5" {{ old('posts_per_week')==5?'selected':'' }}>5 (L-V)</option>
                        <option value="3" {{ old('posts_per_week')==3?'selected':'' }}>3</option>
                        <option value="2" {{ old('posts_per_week')==2?'selected':'' }}>2 (mantenimiento)</option>
                        <option value="1" {{ old('posts_per_week')==1?'selected':'' }}>1</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Temas a generar *</label>
                    <input type="number" name="topic_count" class="form-input" min="5" max="40" value="{{ old('topic_count', 30) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Hora de publicación</label>
                    <input type="time" name="publish_hour" class="form-input" value="{{ old('publish_hour', '08:00') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mezcla de temas (opcional — default: jerarquía de marca)</label>
                <input type="text" name="mezcla" class="form-input" placeholder="~40% predios/zonificación, ~20% herencias, ~25% colonias, ~15% mercado" value="{{ old('mezcla') }}">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Crear y generar mapa de temas →</button>
            </div>
        </form>
    </div>
</div>
@endsection
