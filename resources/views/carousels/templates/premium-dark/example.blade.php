@extends('carousels.templates.premium-dark._base')

@section('body')
<div style="
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 80px 72px 56px;
    position: relative;
">
    {{-- Label --}}
    <div style="margin-bottom: 36px;">
        <span class="chip">Caso real</span>
    </div>

    {{-- Headline --}}
    <h2 style="
        font-size: 54px;
        font-weight: 800;
        line-height: 1.13;
        letter-spacing: -0.5px;
        color: var(--white);
        margin-bottom: 28px;
        max-width: 820px;
    ">{{ $slide->headline }}</h2>

    <div class="accent-line"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px;
        font-weight: 600;
        color: var(--accent-light);
        margin-bottom: 24px;
        line-height: 1.4;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($slide->body ?? null)
    <div style="
        background: rgba(255,255,255,0.04);
        border-left: 4px solid var(--accent-light);
        border-radius: 0 8px 8px 0;
        padding: 28px 32px;
        margin-top: 8px;
        flex: 1;
    ">
        <p style="
            font-size: 23px;
            font-weight: 400;
            color: var(--gray-2);
            line-height: 1.65;
        ">{{ $slide->body }}</p>
    </div>
    @endif

    @if($slide->cta_text ?? null)
    <div style="
        margin-top: 32px;
        display: flex; align-items: center; gap: 14px;
    ">
        <div style="width: 32px; height: 2px; background: var(--accent-light);"></div>
        <span style="font-size: 18px; color: var(--accent-light); font-weight: 600;">{{ $slide->cta_text }}</span>
    </div>
    @endif
</div>
@endsection
