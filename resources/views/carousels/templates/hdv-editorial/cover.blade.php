@extends('carousels.templates.hdv-editorial._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    justify-content: center; padding: 64px 72px 48px;
">
    @if($carousel->type)
    <div style="margin-bottom: 32px;">
        <span class="chip">{{ strtoupper($carousel->type === 'educational' ? 'Educativo' : ($carousel->type === 'commercial' ? 'Inmueble' : ($carousel->type === 'informative' ? 'Informe' : ($carousel->type === 'capture' ? 'Captación' : 'Marca')))) }}</span>
    </div>
    @endif

    <h1 style="
        font-size: 80px; font-weight: 700;
        line-height: 1.02; letter-spacing: -3px;
        color: var(--black); margin-bottom: 36px;
        max-width: 900px; font-family: Georgia, serif;
    ">{{ $slide->headline ?? $carousel->title }}</h1>

    <div class="accent-line"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 26px; font-weight: 400;
        color: var(--gray-2); line-height: 1.55; max-width: 720px;
        font-family: -apple-system, Arial, sans-serif;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($carousel->cta)
    <div style="
        margin-top: 52px; padding-top: 28px;
        border-top: 1px solid var(--border);
    ">
        <span style="
            font-size: 17px; color: var(--accent); font-weight: 700;
            letter-spacing: 0.5px; text-transform: uppercase; font-size: 14px;
            letter-spacing: 2px; font-family: -apple-system, Arial, sans-serif;
        ">{{ $carousel->cta }}</span>
    </div>
    @endif
</div>
@endsection
