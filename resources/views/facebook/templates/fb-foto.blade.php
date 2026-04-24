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
    flex-direction: column;
    justify-content: flex-end;
    padding: 0 72px 56px;
}
/* Background image */
.bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
/* Gradient overlay: transparent top → dark bottom */
.bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(12,26,46,0.10) 0%,
        rgba(12,26,46,0.20) 40%,
        rgba(12,26,46,0.75) 68%,
        rgba(12,26,46,0.92) 100%
    );
    z-index: 1;
}
/* Fallback gradient when no image */
.bg-fallback {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, #1e3a8a 0%, #0C1A2E 100%);
    z-index: 0;
}
.content {
    position: relative; z-index: 2;
}
.accent-line {
    width: 50px; height: 4px;
    background: #3B82F6; border-radius: 2px;
    margin-bottom: 18px;
}
.headline {
    font-size: 60px; font-weight: 800; line-height: 1.05;
    letter-spacing: -1.8px; color: #fff;
    max-width: 820px;
    margin-bottom: 16px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.4);
}
.subheadline {
    font-size: 24px; font-weight: 400; line-height: 1.45;
    color: rgba(255,255,255,0.82); max-width: 680px;
    text-shadow: 0 1px 8px rgba(0,0,0,0.4);
}
/* Logo bottom-right */
.logo {
    position: absolute; bottom: 22px; right: 28px;
    font-size: 13px; font-weight: 700; letter-spacing: 2.5px;
    color: rgba(255,255,255,0.7); text-transform: uppercase;
    font-style: italic; z-index: 3;
}
.logo span { color: #3B82F6; }
</style>
</head>
<body>
<div class="canvas">
    @if($post->background_image_path)
    @php
        $bgPath = \Illuminate\Support\Facades\Storage::disk('public')->path($post->background_image_path);
        $bgSrc  = file_exists($bgPath)
            ? 'data:' . (mime_content_type($bgPath) ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($bgPath))
            : '';
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

    <div class="content">
        <div class="accent-line"></div>

        @if($post->headline)
        <div class="headline">{{ $post->headline }}</div>
        @endif

        @if($post->subheadline)
        <div class="subheadline">{{ $post->subheadline }}</div>
        @endif
    </div>

    <div class="logo">HOME<span>DELVALLE</span></div>
</div>
</body>
</html>
