@extends('carousels.templates.hdv-marino._base')

@section('body')
<div style="
    flex: 1; display: flex; flex-direction: column;
    justify-content: center; padding: 80px 72px 48px 80px;
">
    @if($carousel->type)
    <div style="margin-bottom: 40px;">
        <span class="chip">{{ strtoupper($carousel->type === 'educational' ? 'Educativo' : ($carousel->type === 'commercial' ? 'Inmueble' : ($carousel->type === 'informative' ? 'Informe' : ($carousel->type === 'capture' ? 'Captación' : 'Marca')))) }}</span>
    </div>
    @endif

    <h1 style="
        font-size: 72px; font-weight: 900;
        line-height: 1.06; letter-spacing: -2.5px;
        color: var(--white); margin-bottom: 36px;
        max-width: 840px;
    ">{{ $slide->headline ?? $carousel->title }}</h1>

    <div class="accent-line"></div>

    @if($slide->subheadline ?? null)
    <p style="
        font-size: 28px; font-weight: 400;
        color: var(--gray-2); line-height: 1.5; max-width: 700px;
    ">{{ $slide->subheadline }}</p>
    @endif

    @if($carousel->cta)
    <div style="margin-top: 56px; display: flex; align-items: center; gap: 18px;">
        <div style="width: 40px; height: 2px; background: var(--accent-light);"></div>
        <span style="font-size: 19px; color: var(--gray-2); font-weight: 500; letter-spacing: 0.3px;">{{ $carousel->cta }}</span>
    </div>
    @endif
</div>
@endsection
