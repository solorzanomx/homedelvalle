@extends('carousels.templates.hdv-editorial._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    padding: 56px 72px 48px;
">
    <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 44px;">
        <div class="num-badge">{{ $slide->order }}</div>
        <div style="flex: 1; height: 1px; background: var(--border);"></div>
    </div>

    @if($slide->headline ?? null)
    <h2 style="
        font-size: 62px; font-weight: 700;
        line-height: 1.08; letter-spacing: -2px;
        color: var(--black); margin-bottom: 28px;
        max-width: 860px; font-family: Georgia, serif;
    ">{{ $slide->headline }}</h2>
    @endif

    <div class="accent-line"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px; font-weight: 600;
        color: var(--accent); margin-bottom: 22px; line-height: 1.4;
        font-family: -apple-system, Arial, sans-serif;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($slide->body ?? null)
    <p style="
        font-size: 23px; font-weight: 400;
        color: #444; line-height: 1.7;
        max-width: 820px; flex: 1;
        font-family: -apple-system, Arial, sans-serif;
    ">{{ $slide->body }}</p>
    @endif

    @if($slide->cta_text ?? null)
    <div style="
        margin-top: auto; padding-top: 24px;
        border-top: 1px solid var(--border);
    ">
        <span style="font-size: 15px; color: var(--accent); font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-family: -apple-system, Arial, sans-serif;">
            {{ $slide->cta_text }}
        </span>
    </div>
    @endif
</div>
@endsection
