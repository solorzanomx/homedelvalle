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
    background: #0C1A2E;
    color: #fff;
}
.canvas {
    width: 1200px; height: 628px;
    background: #0C1A2E;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 64px 80px 64px 80px;
}
.bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
.bg-overlay {
    position: absolute; inset: 0;
    background: #0C1A2E;
    z-index: 1;
}
/* Decorative dots top-right */
.canvas::before {
    content: '';
    position: absolute; top: 0; right: 0;
    width: 340px; height: 340px;
    background-image: radial-gradient(circle, rgba(37,99,160,0.25) 1.5px, transparent 1.5px);
    background-size: 22px 22px;
    pointer-events: none;
    z-index: 2;
}
/* Accent glow bottom-left */
.canvas::after {
    content: '';
    position: absolute; bottom: -100px; left: -80px;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37,99,160,0.18) 0%, transparent 70%);
    pointer-events: none;
    z-index: 2;
}
.headline {
    font-size: 56px; font-weight: 800; line-height: 1.1;
    letter-spacing: -1.5px; color: #fff;
    max-width: 680px;
    margin-bottom: 20px;
    position: relative; z-index: 3;
}
.accent-line {
    width: 60px; height: 4px;
    background: #3B82F6; border-radius: 2px;
    margin-bottom: 20px;
    position: relative; z-index: 3;
}
.subheadline {
    font-size: 24px; font-weight: 400; line-height: 1.5;
    color: #8C9AB0; max-width: 600px;
    position: relative; z-index: 3;
}
.body-text {
    font-size: 18px; font-weight: 400; line-height: 1.6;
    color: rgba(255,255,255,0.6); max-width: 580px;
    margin-top: 16px;
    position: relative; z-index: 3;
}
.logo {
    position: absolute; bottom: 20px; right: 28px;
    height: 36px; width: auto;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.85;
    z-index: 4;
}
.stripe {
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 6px; background: #2563A0; z-index: 4;
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
    <img class="bg-image" src="{{ $bgSrc }}" alt="">
    @endif
    @endif
    <div class="bg-overlay" style="opacity:{{ $opacity }};"></div>

    <div class="stripe"></div>

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
