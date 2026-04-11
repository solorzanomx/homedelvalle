@extends('layouts.app-sidebar')
@section('title', $campaign ? 'Editar Campana' : 'Nueva Campana')

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.newsletters.campaigns') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Campanas</a>
</div>

<div style="background:var(--card); border:1px solid var(--border); border-radius:10px; max-width:860px; overflow:hidden;">
    <div style="padding:1rem 1.5rem; border-bottom:1px solid var(--border); font-weight:700;">
        {{ $campaign ? 'Editar: ' . $campaign->subject : 'Nueva Campana' }}
        <span style="float:right; font-size:0.78rem; color:var(--text-muted);">{{ $activeSubscribers }} suscriptores activos</span>
    </div>
    <div style="padding:1.5rem;">
        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:1rem;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        <form method="POST" action="{{ $campaign ? route('admin.newsletters.campaigns.update', $campaign) : route('admin.newsletters.campaigns.store') }}">
            @csrf
            @if($campaign) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Asunto <span style="color:var(--danger);">*</span></label>
                <input type="text" name="subject" class="form-input" value="{{ old('subject', $campaign->subject ?? '') }}" required placeholder="Asunto del newsletter">
            </div>

            <div class="form-group">
                <label class="form-label">Contenido HTML <span style="color:var(--danger);">*</span></label>
                <textarea name="body" id="newsletterBody" class="form-textarea" rows="20">{{ old('body', $campaign->body ?? '') }}</textarea>
            </div>

            <div class="form-actions" style="margin-top:1.5rem;">
                <a href="{{ route('admin.newsletters.campaigns') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $campaign ? 'Guardar Cambios' : 'Crear Borrador' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@include('partials.tinymce', ['editorId' => 'newsletterBody', 'editorHeight' => 400, 'withImageUpload' => true])
@endsection
