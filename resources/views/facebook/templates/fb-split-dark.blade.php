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
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: stretch;
}
/* Background image — full bleed */
.bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
.bg-fallback {
    position: absolute; inset: 0;
    background: #0C1A2E; z-index: 0;
}
/* Dark gradient from right side — covers right ~58% */
.bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        to left,
        rgba(10, 18, 32, 0.97) 0%,
        rgba(10, 18, 32, 0.93) 35%,
        rgba(10, 18, 32, 0.70) 55%,
        rgba(10, 18, 32, 0.10) 75%,
        transparent 100%
    );
    z-index: 1;
}
/* Additional opacity layer controlled by slider */
.bg-opacity-layer {
    position: absolute; inset: 0;
    background: #0C1A2E;
    z-index: 2;
}
/* Right content panel */
.content {
    position: absolute;
    top: 0; right: 0;
    width: 560px; height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 56px 56px 80px;
    z-index: 3;
}
.tag {
    font-size: 11px; font-weight: 700; letter-spacing: 3px;
    text-transform: uppercase; color: rgba(255,255,255,0.5);
    margin-bottom: 24px;
}
.headline {
    font-size: 44px; font-weight: 800; line-height: 1.1;
    letter-spacing: -1.2px; color: #fff;
    margin-bottom: 16px;
}
.accent-line {
    width: 48px; height: 4px;
    background: #3B82F6; border-radius: 2px;
    margin-bottom: 18px;
}
.subheadline {
    font-size: 20px; font-weight: 400; line-height: 1.5;
    color: rgba(255,255,255,0.70);
    margin-bottom: 12px;
}
.body-text {
    font-size: 16px; font-weight: 400; line-height: 1.6;
    color: rgba(255,255,255,0.45);
}
/* Logo bottom-right of content area */
.logo {
    position: absolute; bottom: 22px; right: 36px;
    height: 32px; width: auto;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.80;
    z-index: 4;
}
/* Thin blue vertical accent line separating photo from dark panel */
.divider {
    position: absolute;
    top: 40px; bottom: 40px;
    left: 640px;
    width: 2px;
    background: linear-gradient(to bottom, transparent, #3B82F6 30%, #3B82F6 70%, transparent);
    z-index: 3;
    opacity: 0.5;
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
    @else
    <div class="bg-fallback"></div>
    @endif
    @else
    <div class="bg-fallback"></div>
    @endif

    <div class="bg-overlay"></div>
    <div class="bg-opacity-layer" style="opacity:{{ max(0, $opacity - 0.45) }};"></div>

    <div class="divider"></div>

    <div class="content">
        <div class="tag">Home del Valle · Bienes Raíces CDMX</div>

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
    </div>

    @if($logoSrc ?? null)
    <img class="logo" src="{{ $logoSrc }}" alt="Home del Valle">
    @endif
</div>
</body>
</html>
