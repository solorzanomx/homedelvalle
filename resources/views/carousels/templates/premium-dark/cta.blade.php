@extends('carousels.templates.premium-dark._base')

@section('body')
<div style="
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 72px;
    text-align: center;
    position: relative;
">
    {{-- Radial glow --}}
    <div style="
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 780px; height: 780px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,160,0.25) 0%, transparent 65%);
        pointer-events: none;
    "></div>

    {{-- Top chip --}}
    <div style="margin-bottom: 44px; position: relative; z-index: 1;">
        <span class="chip">¿Listo para actuar?</span>
    </div>

    {{-- Main CTA headline --}}
    <h2 style="
        font-size: 62px;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -1.5px;
        color: var(--white);
        max-width: 800px;
        margin-bottom: 28px;
        position: relative;
        z-index: 1;
    ">{{ $slide->headline ?? $carousel->cta ?? 'Contáctanos hoy' }}</h2>

    <div style="
        width: 80px; height: 4px;
        background: var(--gold);
        border-radius: 2px;
        margin-bottom: 36px;
        position: relative; z-index: 1;
    "></div>

    @if($slide->body ?? null)
    <p style="
        font-size: 24px;
        color: var(--gray-2);
        line-height: 1.55;
        max-width: 700px;
        margin-bottom: 52px;
        position: relative; z-index: 1;
    ">{{ $slide->body }}</p>
    @endif

    {{-- CTA button style --}}
    <div style="
        background: var(--accent);
        color: var(--white);
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 22px 64px;
        border-radius: 8px;
        position: relative;
        z-index: 1;
        box-shadow: 0 8px 32px rgba(37,99,160,0.40);
    ">{{ $slide->cta_text ?? $carousel->cta ?? 'Agendar cita' }}</div>

    {{-- Social / contact hint --}}
    @if($slide->subheadline ?? null)
    <p style="
        margin-top: 36px;
        font-size: 18px;
        color: var(--gray-2);
        position: relative; z-index: 1;
    ">{{ $slide->subheadline }}</p>
    @endif
</div>
@endsection
