@extends('carousels.templates.hdv-foto-limpia._base')

@section('body')
{{-- Number badge floating at top --}}
<div style="padding: 44px 56px 0; align-self: flex-start;">
    <div class="num-badge">{{ $slide->order }}</div>
</div>

{{-- Content panel anchored to the bottom --}}
<div class="content-panel" style="margin-top: auto;">
    <div class="accent-line"></div>

    @if($slide->headline ?? null)
    <h2 style="
        font-size: 52px; font-weight: 800;
        line-height: 1.1; letter-spacing: -1px;
        color: var(--white); margin-bottom: 18px; max-width: 880px;
        text-shadow: 0 2px 8px rgba(0,0,0,0.4);
    ">{{ $slide->headline }}</h2>
    @endif

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 22px; font-weight: 600;
        color: var(--accent-light); margin-bottom: 14px; line-height: 1.4;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($slide->body ?? null)
    <p style="
        font-size: 21px; font-weight: 400;
        color: rgba(255,255,255,0.72); line-height: 1.6; max-width: 820px;
    ">{{ $slide->body }}</p>
    @endif

    @if($slide->cta_text ?? null)
    <div style="margin-top: 22px; display: flex; align-items: center; gap: 12px;">
        <div style="width: 28px; height: 2px; background: var(--accent-light);"></div>
        <span style="font-size: 17px; color: var(--accent-light); font-weight: 600;">{{ $slide->cta_text }}</span>
    </div>
    @endif
</div>
@endsection
