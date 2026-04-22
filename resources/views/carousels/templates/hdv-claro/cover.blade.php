@extends('carousels.templates.hdv-claro._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    justify-content: center; padding: 80px 72px 48px;
    position: relative;
">
    {{-- Background circle --}}
    <div style="
        position: absolute; bottom: -80px; right: -80px;
        width: 480px; height: 480px; border-radius: 50%;
        background: radial-gradient(circle, rgba(59,130,196,0.08) 0%, transparent 70%);
        pointer-events: none;
    "></div>

    @if($carousel->type)
    <div style="margin-bottom: 36px;">
        <span class="chip">{{ strtoupper($carousel->type === 'educational' ? 'Educativo' : ($carousel->type === 'commercial' ? 'Inmueble' : ($carousel->type === 'informative' ? 'Informe' : ($carousel->type === 'capture' ? 'Captación' : 'Marca')))) }}</span>
    </div>
    @endif

    <h1 style="
        font-size: 68px; font-weight: 800;
        line-height: 1.1; letter-spacing: -2px;
        color: var(--navy); margin-bottom: 28px; max-width: 860px;
    ">{{ $slide->headline ?? $carousel->title }}</h1>

    <div class="accent-line"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 28px; font-weight: 400;
        color: var(--gray-2); line-height: 1.5; max-width: 700px;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($carousel->cta)
    <div style="margin-top: 52px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 36px; height: 2px; background: var(--accent);"></div>
        <span style="font-size: 18px; color: var(--gray-2); font-weight: 500;">{{ $carousel->cta }}</span>
    </div>
    @endif
</div>
@endsection
