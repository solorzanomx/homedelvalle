@extends('carousels.templates.hdv-foto-limpia._base')

@section('body')
{{-- Top chip floating above the photo --}}
@if($carousel->type)
<div style="padding: 44px 56px 0; align-self: flex-start;">
    <span class="chip">{{ strtoupper($carousel->type === 'educational' ? 'Educativo' : ($carousel->type === 'commercial' ? 'Inmueble' : ($carousel->type === 'informative' ? 'Informe' : ($carousel->type === 'capture' ? 'Captación' : 'Marca')))) }}</span>
</div>
@endif

{{-- Content panel anchored to the bottom --}}
<div class="content-panel" style="margin-top: auto;">
    <div class="accent-line"></div>

    <h1 style="
        font-size: 58px; font-weight: 800;
        line-height: 1.1; letter-spacing: -1.5px;
        color: var(--white); margin-bottom: 20px;
        max-width: 900px;
        text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    ">{{ $slide->headline ?? $carousel->title }}</h1>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 24px; font-weight: 400;
        color: rgba(255,255,255,0.78); line-height: 1.45;
        max-width: 780px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.5);
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($carousel->cta)
    <div style="margin-top: 28px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 30px; height: 2px; background: var(--accent-light);"></div>
        <span style="font-size: 17px; color: rgba(255,255,255,0.6); font-weight: 500;">{{ $carousel->cta }}</span>
    </div>
    @endif
</div>
@endsection
