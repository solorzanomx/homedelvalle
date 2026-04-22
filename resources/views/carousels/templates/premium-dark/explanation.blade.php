@extends('carousels.templates.premium-dark._base')

@section('body')
<div style="
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 80px 72px 56px;
    position: relative;
">
    {{-- Slide number --}}
    <div style="margin-bottom: 40px;">
        <div class="num-badge">{{ $slide->order }}</div>
    </div>

    {{-- Title --}}
    <h2 style="
        font-size: 52px;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -1px;
        color: var(--white);
        margin-bottom: 28px;
        max-width: 840px;
    ">{{ $slide->headline }}</h2>

    <div class="accent-line"></div>

    {{-- Subheadline (smaller supporting point) --}}
    @if($slide->subheadline ?? null)
    <p style="
        font-size: 24px;
        font-weight: 600;
        color: var(--accent-light);
        margin-bottom: 22px;
        line-height: 1.4;
    ">{{ $slide->subheadline }}</p>
    @endif

    {{-- Body --}}
    @if($slide->body ?? null)
    @php $paragraphs = array_filter(explode("\n", $slide->body)); @endphp
    <div style="flex: 1;">
        @foreach($paragraphs as $p)
        <p style="
            font-size: 23px;
            font-weight: 400;
            color: var(--gray-2);
            line-height: 1.65;
            margin-bottom: 16px;
        ">{{ $p }}</p>
        @endforeach
    </div>
    @endif

    {{-- Footer accent --}}
    <div style="
        margin-top: auto;
        padding-top: 28px;
        border-top: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        gap: 14px;
    ">
        <div style="width: 32px; height: 2px; background: var(--accent-light); flex-shrink: 0;"></div>
        <span style="font-size: 16px; color: rgba(255,255,255,0.35); font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase;">
            HOMEDELVALLE
        </span>
    </div>
</div>
@endsection
