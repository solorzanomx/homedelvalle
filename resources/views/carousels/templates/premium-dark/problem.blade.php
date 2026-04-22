@extends('carousels.templates.premium-dark._base')

@section('body')
<div style="
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 80px 72px 56px;
    position: relative;
">
    {{-- Warning symbol --}}
    <div style="
        width: 72px; height: 72px;
        border-radius: 16px;
        background: rgba(59,130,246,0.10);
        border: 2px solid rgba(59,130,246,0.30);
        display: flex; align-items: center; justify-content: center;
        font-size: 36px;
        margin-bottom: 40px;
    ">⚠</div>

    {{-- Label --}}
    <div style="margin-bottom: 28px;">
        <span class="chip">El problema</span>
    </div>

    {{-- Headline --}}
    <h2 style="
        font-size: 58px;
        font-weight: 800;
        line-height: 1.13;
        letter-spacing: -1px;
        color: var(--white);
        margin-bottom: 28px;
        max-width: 820px;
    ">{{ $slide->headline }}</h2>

    <div class="accent-line"></div>

    {{-- Subheadline --}}
    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px;
        font-weight: 600;
        color: var(--accent-light);
        margin-bottom: 22px;
        line-height: 1.4;
    ">{{ $slide->subheadline }}</p>
    @endif

    {{-- Body --}}
    @if($slide->body ?? null)
    <p style="
        font-size: 24px;
        font-weight: 400;
        color: var(--gray-2);
        line-height: 1.65;
        max-width: 800px;
    ">{{ $slide->body }}</p>
    @endif
</div>
@endsection
