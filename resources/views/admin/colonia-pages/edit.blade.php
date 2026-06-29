@extends('layouts.app-sidebar')
@section('title', $colonia->exists ? 'Editar: ' . $colonia->name : 'Nueva colonia')

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $colonia->exists ? 'Editar: ' . $colonia->name : 'Nueva landing page de colonia' }}</h2>
        <p class="text-muted">Contenido SEO + FAQs para la página /{{ $colonia->slug ?: 'slug' }}</p>
    </div>
    <a href="{{ route('admin.colonia-pages.index') }}" class="btn btn-outline">← Volver</a>
</div>

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:1rem;">
    <ul style="margin:0;padding-left:1rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ $colonia->exists ? route('admin.colonia-pages.update', $colonia) : route('admin.colonia-pages.store') }}"
      method="POST">
    @csrf
    @if($colonia->exists) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">

        {{-- ── Columna principal ── --}}
        <div style="display:flex;flex-direction:column;gap:1.5rem;">

            {{-- Identidad --}}
            <div class="card">
                <div class="card-header"><strong>Identidad</strong></div>
                <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="grid-column:span 1;">
                        <label class="form-label">Nombre de la colonia *</label>
                        <input name="name" type="text" class="form-control" value="{{ old('name', $colonia->name) }}" required placeholder="Narvarte Poniente">
                    </div>
                    <div class="form-group" style="grid-column:span 1;">
                        <label class="form-label">Slug (URL) *</label>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <span class="text-muted" style="font-size:0.9rem;">homedelvalle.mx/</span>
                            <input name="slug" type="text" class="form-control" value="{{ old('slug', $colonia->slug) }}" required placeholder="narvarte">
                        </div>
                    </div>
                    <div class="form-group" style="grid-column:span 2;">
                        <label class="form-label">Términos de búsqueda de propiedades</label>
                        <input name="colony_search_terms" type="text" class="form-control"
                               value="{{ old('colony_search_terms', $colonia->colony_search_terms) }}"
                               placeholder="narvarte,narvarte poniente,narvarte oriente">
                        <small class="text-muted">Separados por coma. Se usan para filtrar propiedades de la BD que coincidan con estos valores en el campo "colonia".</small>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card">
                <div class="card-header"><strong>SEO</strong></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Meta title <small class="text-muted">(max 70 car.)</small></label>
                        <input name="meta_title" type="text" class="form-control" maxlength="70"
                               value="{{ old('meta_title', $colonia->meta_title) }}"
                               placeholder="Propiedades en Narvarte, Benito Juárez | Home del Valle">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta description <small class="text-muted">(max 160 car.)</small></label>
                        <textarea name="meta_description" class="form-control" rows="3" maxlength="160"
                                  placeholder="Departamentos en venta y renta en Narvarte Poniente, CDMX...">{{ old('meta_description', $colonia->meta_description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Contenido del hero --}}
            <div class="card">
                <div class="card-header"><strong>Contenido del hero</strong></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Heading (H1)</label>
                        <input name="heading" type="text" class="form-control"
                               value="{{ old('heading', $colonia->heading) }}"
                               placeholder="Propiedades en Narvarte">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subheading</label>
                        <textarea name="subheading" class="form-control" rows="2"
                                  placeholder="La colonia más vibrante de Benito Juárez...">{{ old('subheading', $colonia->subheading) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Sobre la colonia --}}
            <div class="card">
                <div class="card-header"><strong>Descripción de la colonia</strong> <small class="text-muted">(HTML permitido)</small></div>
                <div class="card-body">
                    <textarea name="about" class="form-control" rows="8"
                              placeholder="<p>Narvarte se ha consolidado como...</p>">{{ old('about', $colonia->about) }}</textarea>
                </div>
            </div>

            {{-- FAQs --}}
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <strong>Preguntas frecuentes (FAQPage schema)</strong>
                    <button type="button" id="addFaq" class="btn btn-sm btn-outline">+ Agregar FAQ</button>
                </div>
                <div class="card-body">
                    <div id="faqList" style="display:flex;flex-direction:column;gap:1rem;">
                        @php $faqs = old('faq_q') ? array_map(fn($q,$a) => ['q'=>$q,'a'=>$a], old('faq_q',[]), old('faq_a',[])) : ($colonia->faqs ?? []) @endphp
                        @foreach($faqs as $i => $faq)
                        <div class="faq-item" style="border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem;">
                            <div style="display:flex;justify-content:flex-end;margin-bottom:0.5rem;">
                                <button type="button" class="btn btn-sm btn-danger remove-faq">Eliminar</button>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pregunta</label>
                                <input name="faq_q[]" type="text" class="form-control" value="{{ $faq['q'] }}" placeholder="¿Cuánto cuesta...?">
                            </div>
                            <div class="form-group" style="margin-top:0.5rem;">
                                <label class="form-label">Respuesta</label>
                                <textarea name="faq_a[]" class="form-control" rows="3">{{ $faq['a'] }}</textarea>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if(empty($faqs))
                    <p class="text-muted" style="font-size:0.85rem;">Sin FAQs todavía. Agrega preguntas frecuentes para mejorar el SEO con rich snippets.</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Sidebar ── --}}
        <div style="display:flex;flex-direction:column;gap:1rem;">
            <div class="card">
                <div class="card-header"><strong>Publicación</strong></div>
                <div class="card-body">
                    <div class="form-group">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1"
                                   {{ old('is_published', $colonia->is_published) ? 'checked' : '' }}>
                            <span class="form-label" style="margin:0;">Publicar página</span>
                        </label>
                        <small class="text-muted">La página solo es accesible si está publicada.</small>
                    </div>
                    <div class="form-group" style="margin-top:1rem;">
                        <label class="form-label">Orden (ascendente)</label>
                        <input name="sort_order" type="number" class="form-control" value="{{ old('sort_order', $colonia->sort_order ?? 0) }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        {{ $colonia->exists ? 'Guardar cambios' : 'Crear colonia' }}
                    </button>
                </div>
            </div>

            @if($colonia->exists && $colonia->is_published)
            <div class="card">
                <div class="card-body">
                    <p class="text-muted" style="font-size:0.82rem;margin-bottom:0.5rem;">URL pública:</p>
                    <a href="{{ url('/' . $colonia->slug) }}" target="_blank"
                       class="text-primary" style="font-size:0.85rem;word-break:break-all;">
                        {{ url('/' . $colonia->slug) }}
                    </a>
                </div>
            </div>
            @endif
        </div>

    </div>
</form>

<template id="faqTemplate">
    <div class="faq-item" style="border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem;">
        <div style="display:flex;justify-content:flex-end;margin-bottom:0.5rem;">
            <button type="button" class="btn btn-sm btn-danger remove-faq">Eliminar</button>
        </div>
        <div class="form-group">
            <label class="form-label">Pregunta</label>
            <input name="faq_q[]" type="text" class="form-control" placeholder="¿Cuánto cuesta...?">
        </div>
        <div class="form-group" style="margin-top:0.5rem;">
            <label class="form-label">Respuesta</label>
            <textarea name="faq_a[]" class="form-control" rows="3"></textarea>
        </div>
    </div>
</template>

@section('scripts')
<script>
document.getElementById('addFaq').addEventListener('click', function () {
    const tpl = document.getElementById('faqTemplate').content.cloneNode(true);
    document.getElementById('faqList').appendChild(tpl);
});
document.getElementById('faqList').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-faq')) {
        e.target.closest('.faq-item').remove();
    }
});
</script>
@endsection

@endsection
