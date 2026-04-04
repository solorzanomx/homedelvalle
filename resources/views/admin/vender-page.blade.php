@extends('layouts.app-sidebar')
@section('title', 'Pagina Vender')

@section('content')
<div class="page-header">
    <div>
        <h2>Pagina Vender</h2>
        <p class="text-muted">Administra el contenido de la pagina publica /vende-tu-propiedad</p>
    </div>
    <a href="{{ url('/vende-tu-propiedad') }}" target="_blank" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:.5rem;">
        <x-icon name="external-link" class="w-4 h-4" />
        Ver pagina
    </a>
</div>

<form method="POST" action="{{ route('admin.vender-page.update') }}">
    @csrf

    @php $content = $settings?->vender_content ?? []; @endphp

    {{-- 1. Hero --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Hero</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Badge</label>
                <input type="text" name="badge" class="form-input" value="{{ old('badge', $content['badge'] ?? '') }}" placeholder="Ej: #1 en ventas">
            </div>
            <div class="form-group">
                <label class="form-label">Titulo principal</label>
                <input type="text" name="heading" class="form-input" value="{{ old('heading', $content['heading'] ?? '') }}" placeholder="Vende tu propiedad al mejor precio">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitulo</label>
                <textarea name="subheading" class="form-textarea" rows="2" placeholder="Descripcion breve del servicio de venta...">{{ old('subheading', $content['subheading'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 2. Beneficios --}}
    @php
        $defaultBenefits = [
            ['title' => '', 'desc' => ''],
            ['title' => '', 'desc' => ''],
            ['title' => '', 'desc' => ''],
            ['title' => '', 'desc' => ''],
        ];
        $benefits = $content['benefits'] ?? $defaultBenefits;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Beneficios</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach($benefits as $i => $benefit)
                <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;">
                    <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Beneficio {{ $i + 1 }}</div>
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="benefits[{{ $i }}][title]" class="form-input" value="{{ old("benefits.$i.title", $benefit['title'] ?? '') }}" placeholder="Titulo del beneficio">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripcion</label>
                        <input type="text" name="benefits[{{ $i }}][desc]" class="form-input" value="{{ old("benefits.$i.desc", $benefit['desc'] ?? '') }}" placeholder="Descripcion breve">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 3. Metricas --}}
    @php
        $defaultMetrics = [
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
        ];
        $metrics = $content['metrics'] ?? $defaultMetrics;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Metricas</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach($metrics as $i => $metric)
                <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;">
                    <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Metrica {{ $i + 1 }}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Valor</label>
                            <input type="text" name="metrics[{{ $i }}][value]" class="form-input" value="{{ old("metrics.$i.value", $metric['value'] ?? '') }}" placeholder="Ej: 98%">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input type="text" name="metrics[{{ $i }}][label]" class="form-input" value="{{ old("metrics.$i.label", $metric['label'] ?? '') }}" placeholder="Ej: Clientes satisfechos">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 4. Proceso --}}
    @php
        $defaultSteps = [
            ['num' => '01', 'title' => '', 'desc' => ''],
            ['num' => '02', 'title' => '', 'desc' => ''],
            ['num' => '03', 'title' => '', 'desc' => ''],
        ];
        $steps = $content['process_steps'] ?? $defaultSteps;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Proceso</h3></div>
        <div class="card-body">
            @foreach($steps as $i => $step)
            <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;margin-bottom:0.75rem;">
                <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Paso {{ $i + 1 }}</div>
                <div style="display:grid;grid-template-columns:80px 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Numero</label>
                        <input type="text" name="process_steps[{{ $i }}][num]" class="form-input" value="{{ old("process_steps.$i.num", $step['num'] ?? '') }}" placeholder="01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="process_steps[{{ $i }}][title]" class="form-input" value="{{ old("process_steps.$i.title", $step['title'] ?? '') }}" placeholder="Titulo del paso">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripcion</label>
                    <textarea name="process_steps[{{ $i }}][desc]" class="form-textarea" rows="2" placeholder="Descripcion del paso...">{{ old("process_steps.$i.desc", $step['desc'] ?? '') }}</textarea>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 5. Preguntas Frecuentes --}}
    @php
        $defaultFaqs = [
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
        ];
        $faqs = $content['faqs'] ?? $defaultFaqs;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Preguntas Frecuentes</h3></div>
        <div class="card-body">
            @foreach($faqs as $i => $faq)
            <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;margin-bottom:0.75rem;">
                <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Pregunta {{ $i + 1 }}</div>
                <div class="form-group">
                    <label class="form-label">Pregunta</label>
                    <input type="text" name="faqs[{{ $i }}][q]" class="form-input" value="{{ old("faqs.$i.q", $faq['q'] ?? '') }}" placeholder="Escribe la pregunta">
                </div>
                <div class="form-group">
                    <label class="form-label">Respuesta</label>
                    <textarea name="faqs[{{ $i }}][a]" class="form-textarea" rows="2" placeholder="Escribe la respuesta...">{{ old("faqs.$i.a", $faq['a'] ?? '') }}</textarea>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 6. CTA y SEO --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>CTA y SEO</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo CTA</label>
                    <input type="text" name="cta_heading" class="form-input" value="{{ old('cta_heading', $content['cta_heading'] ?? '') }}" placeholder="Titulo de la seccion CTA">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo CTA</label>
                    <input type="text" name="cta_subheading" class="form-input" value="{{ old('cta_subheading', $content['cta_subheading'] ?? '') }}" placeholder="Subtitulo de la seccion CTA">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Mensaje de WhatsApp</label>
                <textarea name="wa_message" class="form-textarea" rows="2" placeholder="Hola, me interesa vender mi propiedad...">{{ old('wa_message', $content['wa_message'] ?? '') }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Meta titulo (SEO)</label>
                    <input type="text" name="meta_title" class="form-input" value="{{ old('meta_title', $content['meta_title'] ?? '') }}" placeholder="Titulo para buscadores">
                </div>
                <div class="form-group">
                    <label class="form-label">Meta descripcion (SEO)</label>
                    <input type="text" name="meta_description" class="form-input" value="{{ old('meta_description', $content['meta_description'] ?? '') }}" placeholder="Descripcion para buscadores">
                </div>
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div class="p-save-bar">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
@endsection
