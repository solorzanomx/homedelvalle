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
    {{-- Background glow --}}
    <div style="
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 640px; height: 640px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,160,0.18) 0%, transparent 65%);
        pointer-events: none;
    "></div>

    {{-- Label --}}
    @if($slide->subheadline ?? null)
    <div class="chip" style="margin-bottom: 44px;">{{ $slide->subheadline }}</div>
    @else
    <div class="chip" style="margin-bottom: 44px;">Dato clave</div>
    @endif

    {{-- Big stat --}}
    <div style="
        font-size: 148px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -6px;
        color: var(--white);
        position: relative;
        z-index: 1;
    ">{{ $slide->headline ?? '—' }}</div>

    <div style="
        width: 80px; height: 4px;
        background: var(--gold);
        border-radius: 2px;
        margin: 36px 0;
    "></div>

    {{-- Description --}}
    @if($slide->body ?? null)
    <p style="
        font-size: 28px;
        font-weight: 400;
        color: var(--gray-2);
        line-height: 1.5;
        max-width: 720px;
    ">{{ $slide->body }}</p>
    @endif

    {{-- CTA text below body --}}
    @if($slide->cta_text ?? null)
    <div style="
        margin-top: 48px;
        padding: 14px 36px;
        border: 1.5px solid rgba(255,255,255,0.15);
        border-radius: 6px;
        font-size: 18px;
        color: var(--gray-1);
        font-weight: 500;
        letter-spacing: 0.5px;
    ">{{ $slide->cta_text }}</div>
    @endif
</div>
@endsection
