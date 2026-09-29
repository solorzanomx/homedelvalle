@extends('layouts.app-sidebar')
@section('title', 'CTAs del Blog por Cluster')

@section('content')
<div class="page-header">
    <div>
        <h2>CTAs del Blog por Cluster</h2>
        <p class="text-muted">Un CTA por etapa del lector. Se usa en el bloque inline, el final del post, y el mensaje de WhatsApp prellenado. Usa <code>{titulo}</code> en el mensaje de WhatsApp para insertar el título del post.</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">&#8592; Blog Posts</a>
</div>

@foreach($configs as $c)
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header"><h3>{{ \App\Support\BlogCluster::LABELS[$c->cluster] ?? $c->cluster }}</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.blog-ctas.update', $c->id) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de lead que genera</label>
                    <select name="form_type" class="form-input">
                        @foreach(['vendedor'=>'Vendedor','vendedor_predio'=>'Predio → desarrolladora','comprador'=>'Comprador','arrendatario'=>'Arrendatario','propietario_renta'=>'Propietario (renta)','b2b'=>'B2B','contacto'=>'Contacto general'] as $val=>$label)
                            <option value="{{ $val }}" {{ $c->form_type === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div></div>
                <div class="form-group full-width">
                    <label class="form-label">Título</label>
                    <input type="text" name="headline" class="form-input" value="{{ old('headline', $c->headline) }}" required maxlength="200">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Texto</label>
                    <textarea name="body" class="form-textarea" rows="2" required maxlength="600">{{ old('body', $c->body) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Texto del botón</label>
                    <input type="text" name="button_label" class="form-input" value="{{ old('button_label', $c->button_label) }}" required maxlength="80">
                </div>
                <div class="form-group">
                    <label class="form-label">Mensaje de WhatsApp prellenado</label>
                    <input type="text" name="whatsapp_message" class="form-input" value="{{ old('whatsapp_message', $c->whatsapp_message) }}" required maxlength="400">
                </div>

                @if($c->cluster === \App\Support\BlogCluster::HERENCIAS)
                <div style="grid-column:1/-1;border-top:1px dashed var(--border);padding-top:.8rem;margin-top:.4rem;">
                    <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);margin-bottom:.4rem;">Variante — el lector ya decidió vender (posts de "isr-venta", "hermano-no-quiere-vender", "vender-...")</div>
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Título (decidió vender)</label>
                    <input type="text" name="sell_headline" class="form-input" value="{{ old('sell_headline', $c->sell_headline) }}" maxlength="200">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Texto (decidió vender)</label>
                    <textarea name="sell_body" class="form-textarea" rows="2" maxlength="600">{{ old('sell_body', $c->sell_body) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Texto del botón (decidió vender)</label>
                    <input type="text" name="sell_button_label" class="form-input" value="{{ old('sell_button_label', $c->sell_button_label) }}" maxlength="80">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp prellenado (decidió vender)</label>
                    <input type="text" name="sell_whatsapp_message" class="form-input" value="{{ old('sell_whatsapp_message', $c->sell_whatsapp_message) }}" maxlength="400">
                </div>
                @else
                <input type="hidden" name="sell_headline" value="{{ $c->sell_headline }}">
                <input type="hidden" name="sell_body" value="{{ $c->sell_body }}">
                <input type="hidden" name="sell_button_label" value="{{ $c->sell_button_label }}">
                <input type="hidden" name="sell_whatsapp_message" value="{{ $c->sell_whatsapp_message }}">
                @endif
            </div>
            <div class="form-actions"><button type="submit" class="btn btn-primary">Guardar {{ \App\Support\BlogCluster::LABELS[$c->cluster] ?? $c->cluster }}</button></div>
        </form>
    </div>
</div>
@endforeach
@endsection
