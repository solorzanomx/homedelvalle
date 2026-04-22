@extends('carousels.templates.premium-dark._base')

@section('body')
<div style="
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 80px 72px 56px;
    position: relative;
">
    {{-- Top bar with number --}}
    <div style="
        display: flex;
        align-items: center;
        gap: 22px;
        margin-bottom: 52px;
    ">
        <div style="
            width: 64px; height: 64px;
            border-radius: 12px;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; font-weight: 900;
            color: var(--white);
            flex-shrink: 0;
        ">{{ $slide->order }}</div>
        <div style="
            flex: 1; height: 1px;
            background: linear-gradient(to right, rgba(37,99,160,0.6), transparent);
        "></div>
    </div>

    {{-- Headline --}}
    <h2 style="
        font-size: 58px;
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -1px;
        color: var(--white);
        margin-bottom: 32px;
        max-width: 860px;
    ">{{ $slide->headline }}</h2>

    {{-- Subheadline --}}
    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px;
        font-weight: 600;
        color: var(--accent-light);
        margin-bottom: 24px;
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
        max-width: 820px;
        flex: 1;
    ">{{ $slide->body }}</p>
    @endif

    {{-- Bottom checkmark row --}}
    <div style="
        margin-top: auto;
        padding-top: 36px;
        display: flex;
        align-items: center;
        gap: 12px;
    ">
        <div style="
            width: 28px; height: 28px;
            border-radius: 50%;
            background: rgba(59,130,246,0.15);
            border: 1.5px solid var(--accent-light);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        ">✓</div>
        <span style="font-size: 17px; color: var(--accent-light); font-weight: 600; letter-spacing: 0.3px;">
            {{ $slide->cta_text ?? 'Beneficio verificado' }}
        </span>
    </div>
</div>
@endsection
