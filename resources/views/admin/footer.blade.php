@extends('layouts.app-sidebar')
@section('title', 'Editor de Footer')

@section('content')
<div class="page-header">
    <div>
        <h2>Editor de Footer</h2>
        <p class="text-muted">Personaliza el pie de pagina del sitio publico</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.footer.update') }}">
    @csrf

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="card">
            <div class="card-header"><h3>Texto del Footer</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Texto "Sobre nosotros"</label>
                    <textarea name="footer_about" class="form-textarea" rows="4" placeholder="Breve descripcion de la empresa...">{{ old('footer_about', $settings?->footer_about) }}</textarea>
                    <p class="form-hint">Se muestra en la primera columna del footer.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Texto inferior (copyright)</label>
                    <input type="text" name="footer_bottom_text" class="form-input"
                           value="{{ old('footer_bottom_text', $settings?->footer_bottom_text) }}"
                           placeholder="© 2026 Mi Empresa. Todos los derechos reservados.">
                    <p class="form-hint">Se muestra en la barra inferior del footer.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Links del Footer</h3></div>
            <div class="card-body">
                <p class="form-hint" style="margin-bottom: 1rem;">Links adicionales que aparecen en la barra inferior del footer.</p>
                @php $footerLinks = old('footer_links', $settings?->footer_bottom_links ?? []); @endphp
                @for($i = 0; $i < 6; $i++)
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <input type="text" name="footer_links[{{ $i }}][label]" class="form-input" placeholder="Texto" value="{{ $footerLinks[$i]['label'] ?? '' }}">
                    <input type="text" name="footer_links[{{ $i }}][url]" class="form-input" placeholder="URL" value="{{ $footerLinks[$i]['url'] ?? '' }}">
                </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar Footer</button>
    </div>
</form>
@endsection
