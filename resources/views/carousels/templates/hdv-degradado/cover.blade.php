@extends('carousels.templates.hdv-degradado._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    justify-content: center; padding: 80px 72px 48px;
    position: relative;
">
    {{-- Glow orb --}}
    <div style="
        position: absolute; top: -120px; right: -100px;
        width: 600px; height: 600px; border-radius: 50%;
        background: radial-gradient(circle, rgba(147,197,253,0.18) 0%, transparent 65%);
        pointer-events: none;
    "></div>

    @if($carousel->type)
    <div style="margin-bottom: 36px; position: relative; z-index: 1;">
        <span class="chip">{{ strtoupper($carousel->type === 'educational' ? 'Educativo' : ($carousel->type === 'commercial' ? 'Inmueble' : ($carousel->type === 'informative' ? 'Informe' : ($carousel->type === 'capture' ? 'Captación' : 'Marca')))) }}</span>
    </div>
    @endif

    <h1 style="
        font-size: 68px; font-weight: 800;
        line-height: 1.1; letter-spacing: -2px;
        color: var(--white); margin-bottom: 32px;
        max-width: 860px; position: relative; z-index: 1;
    ">{{ $slide->headline ?? $carousel->title }}</h1>

    <div class="accent-line" style="position: relative; z-index: 1;"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 27px; font-weight: 400;
        color: rgba(255,255,255,0.75); line-height: 1.5;
        max-width: 720px; position: relative; z-index: 1;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($carousel->cta)
    <div style="margin-top: 52px; display: flex; align-items: center; gap: 16px; position: relative; z-index: 1;">
        <div style="width: 36px; height: 2px; background: var(--accent-light);"></div>
        <span style="font-size: 18px; color: rgba(255,255,255,0.65); font-weight: 500;">{{ $carousel->cta }}</span>
    </div>
    @endif
</div>
@endsection
