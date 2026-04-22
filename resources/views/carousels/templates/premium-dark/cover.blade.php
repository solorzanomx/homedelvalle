@extends('carousels.templates.premium-dark._base')

@section('body')
<div style="
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 80px 72px 48px;
    position: relative;
">
    {{-- Large background accent circle --}}
    <div style="
        position: absolute;
        bottom: -140px; left: -100px;
        width: 560px; height: 560px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,160,0.20) 0%, transparent 70%);
        pointer-events: none;
    "></div>

    {{-- Chip / type label --}}
    @if($carousel->type)
    <div style="margin-bottom: 36px;">
        <span class="chip">{{ strtoupper($carousel->type === 'educational' ? 'Educativo' : ($carousel->type === 'commercial' ? 'Inmueble' : ($carousel->type === 'informative' ? 'Informe' : ($carousel->type === 'capture' ? 'Captación' : 'Marca')))) }}</span>
    </div>
    @endif

    {{-- Main title --}}
    <h1 style="
        font-size: 64px;
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -1.5px;
        color: var(--white);
        margin-bottom: 32px;
        max-width: 820px;
    ">{{ $slide->headline ?? $carousel->title }}</h1>

    {{-- Accent line --}}
    <div class="accent-line"></div>

    {{-- Subheadline --}}
    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px;
        font-weight: 400;
        color: var(--gray-2);
        line-height: 1.5;
        max-width: 680px;
    ">{{ $slide->subheadline }}</p>
    @endif

    {{-- CTA indicator --}}
    @if($carousel->cta)
    <div style="margin-top: 52px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 36px; height: 2px; background: var(--accent-light);"></div>
        <span style="font-size: 18px; color: var(--gray-2); font-weight: 500; letter-spacing: 0.5px;">
            {{ $carousel->cta }}
        </span>
    </div>
    @endif
</div>
@endsection
