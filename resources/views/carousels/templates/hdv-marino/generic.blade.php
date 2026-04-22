@extends('carousels.templates.hdv-marino._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    padding: 80px 72px 56px 80px;
">
    <div style="margin-bottom: 44px;">
        <div class="num-badge">{{ $slide->order }}</div>
    </div>

    @if($slide->headline ?? null)
    <h2 style="
        font-size: 62px; font-weight: 900;
        line-height: 1.1; letter-spacing: -1.5px;
        color: var(--white); margin-bottom: 32px; max-width: 840px;
    ">{{ $slide->headline }}</h2>
    @endif

    <div class="accent-line"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px; font-weight: 600;
        color: var(--accent-light); margin-bottom: 22px; line-height: 1.4;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($slide->body ?? null)
    <p style="
        font-size: 24px; font-weight: 400;
        color: var(--gray-2); line-height: 1.65;
        max-width: 820px; flex: 1;
    ">{{ $slide->body }}</p>
    @endif

    @if($slide->cta_text ?? null)
    <div style="
        margin-top: auto; padding-top: 30px;
        border-top: 2px solid rgba(59,130,196,0.25);
    ">
        <span style="font-size: 19px; color: var(--accent-light); font-weight: 700; letter-spacing: 0.3px;">
            → {{ $slide->cta_text }}
        </span>
    </div>
    @endif
</div>
@endsection
