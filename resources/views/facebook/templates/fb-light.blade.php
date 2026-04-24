<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body {
    width: 1200px; height: 628px;
    overflow: hidden;
    font-family: -apple-system, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    background: #ffffff;
    color: #1e293b;
}
.canvas {
    width: 1200px; height: 628px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px 80px;
    text-align: center;
}
/* Subtle grid background */
.canvas::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(37,99,160,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(37,99,160,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}
/* Top accent bar */
.top-bar {
    position: absolute; top: 0; left: 0; right: 0;
    height: 6px; background: linear-gradient(90deg, #1e3a8a, #3B82F6);
}
.eyebrow {
    font-size: 13px; font-weight: 700; letter-spacing: 3px;
    text-transform: uppercase; color: #2563A0;
    margin-bottom: 20px;
    position: relative; z-index: 1;
}
.headline {
    font-size: 52px; font-weight: 800; line-height: 1.1;
    letter-spacing: -1.5px; color: #0f172a;
    max-width: 800px;
    margin-bottom: 20px;
    position: relative; z-index: 1;
}
.accent-line {
    width: 60px; height: 4px;
    background: #3B82F6; border-radius: 2px;
    margin: 0 auto 20px;
    position: relative; z-index: 1;
}
.subheadline {
    font-size: 22px; font-weight: 400; line-height: 1.55;
    color: #475569; max-width: 680px;
    position: relative; z-index: 1;
}
.body-text {
    font-size: 17px; font-weight: 400; line-height: 1.6;
    color: #94a3b8; max-width: 600px;
    margin-top: 14px;
    position: relative; z-index: 1;
}
/* Logo bottom-right */
.logo {
    position: absolute; bottom: 20px; right: 28px;
    height: 36px; width: auto;
    object-fit: contain;
    opacity: 0.85;
    z-index: 2;
}
</style>
</head>
<body>
<div class="canvas">
    @php $opacity = $post->bg_overlay_opacity ?? 0.5; @endphp
    @if($post->background_image_path)
    @php
        $bgPath = \Illuminate\Support\Facades\Storage::disk('public')->path($post->background_image_path);
        $bgSrc  = file_exists($bgPath) ? 'data:'.(mime_content_type($bgPath) ?: 'image/png').';base64,'.base64_encode(file_get_contents($bgPath)) : '';
    @endphp
    @if($bgSrc)
    <img style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;" src="{{ $bgSrc }}" alt="">
    @endif
    @endif
    {{-- White overlay — high opacity keeps text readable on light template --}}
    <div style="position:absolute;inset:0;background:#ffffff;opacity:{{ $opacity }};z-index:1;"></div>

    <div class="top-bar" style="z-index:3;"></div>

    <div class="eyebrow">Bienes Raíces · CDMX</div>

    @if($post->headline)
    <div class="headline">{{ $post->headline }}</div>
    @endif

    <div class="accent-line"></div>

    @if($post->subheadline)
    <div class="subheadline">{{ $post->subheadline }}</div>
    @endif

    @if($post->body_text)
    <div class="body-text">{{ $post->body_text }}</div>
    @endif

    @if($logoSrc ?? null)
    <img class="logo" src="{{ $logoSrc }}" alt="Home del Valle">
    @endif
</div>
</body>
</html>
