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
    {{-- Background radial glow --}}
    <div style="
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 700px; height: 700px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(201,168,76,0.07) 0%, transparent 70%);
        pointer-events: none;
    "></div>

    {{-- Quote mark --}}
    <div style="
        font-size: 120px;
        line-height: 1;
        font-family: Georgia, serif;
        color: rgba(201,168,76,0.25);
        margin-bottom: -20px;
        position: relative;
        z-index: 1;
    ">"</div>

    {{-- Headline (testimonial / stat) --}}
    <h2 style="
        font-size: 46px;
        font-weight: 700;
        line-height: 1.22;
        letter-spacing: -0.5px;
        color: var(--white);
        max-width: 820px;
        margin-bottom: 36px;
        position: relative;
        z-index: 1;
    ">{{ $slide->headline }}</h2>

    {{-- Divider dots --}}
    <div style="display: flex; gap: 8px; margin-bottom: 32px;">
        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--gold);"></div>
        <div style="width: 8px; height: 8px; border-radius: 50%; background: rgba(201,168,76,0.4);"></div>
        <div style="width: 8px; height: 8px; border-radius: 50%; background: rgba(201,168,76,0.15);"></div>
    </div>

    {{-- Source / attribution --}}
    @if($slide->subheadline ?? null)
    <p style="
        font-size: 20px;
        font-weight: 600;
        color: var(--gold);
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($slide->body ?? null)
    <p style="
        font-size: 18px;
        color: var(--gray-2);
        line-height: 1.5;
        max-width: 680px;
    ">{{ $slide->body }}</p>
    @endif
</div>
@endsection
