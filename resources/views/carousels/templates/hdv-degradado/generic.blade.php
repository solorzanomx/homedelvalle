@extends('carousels.templates.hdv-degradado._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    padding: 80px 72px 56px; position: relative;
">
    <div style="margin-bottom: 40px;">
        <div class="num-badge">{{ $slide->order }}</div>
    </div>

    @if($slide->headline ?? null)
    <h2 style="
        font-size: 58px; font-weight: 800;
        line-height: 1.12; letter-spacing: -1px;
        color: var(--white); margin-bottom: 28px; max-width: 860px;
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
        color: rgba(255,255,255,0.72); line-height: 1.65;
        max-width: 820px; flex: 1;
    ">{{ $slide->body }}</p>
    @endif

    @if($slide->cta_text ?? null)
    <div style="
        margin-top: auto; padding-top: 28px;
        border-top: 1px solid rgba(255,255,255,0.12);
    ">
        <span style="font-size: 18px; color: var(--accent-light); font-weight: 600;">
            → {{ $slide->cta_text }}
        </span>
    </div>
    @endif
</div>
@endsection
