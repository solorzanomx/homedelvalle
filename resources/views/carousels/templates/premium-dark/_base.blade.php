<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --navy:   #0C1A2E;
    --navy2:  #112240;
    --white:  #FFFFFF;
    --gray-1: #F4F5F7;
    --gray-2: #8C9AB0;
    --accent: #2563A0;
    --accent-light: #3B82F6;
}
html, body {
    width: 1080px;
    height: 1350px;
    overflow: hidden;
    font-family: -apple-system, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    background: var(--navy);
    color: var(--white);
}
.canvas {
    width: 1080px;
    height: 1350px;
    background: var(--navy);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}
/* ── Background image (when available) ── */
.canvas .bg-image {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    z-index: 0;
}
.canvas .bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, rgba(12,26,46,0.55) 0%, rgba(12,26,46,0.32) 50%, rgba(12,26,46,0.62) 100%);
    z-index: 0;
}
/* ── Decorative dots pattern (top-right corner) ── */
.canvas::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 320px; height: 320px;
    background-image: radial-gradient(circle, rgba(37,99,160,0.22) 1.5px, transparent 1.5px);
    background-size: 22px 22px;
    pointer-events: none;
    z-index: 0;
}
.slide-content {
    flex: 1;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
}
/* ── Brand bar ── */
.brand-bar {
    flex-shrink: 0;
    height: 72px;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 52px;
    z-index: 2;
}
.brand-logo {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 3.5px;
    color: var(--white);
    text-transform: uppercase;
    font-style: italic;
}
.brand-logo span {
    color: var(--accent-light);
}
.slide-counter {
    font-size: 17px;
    font-weight: 500;
    color: rgba(255,255,255,0.7);
    letter-spacing: 1.5px;
}
/* ── Accent line ── */
.accent-line {
    width: 56px;
    height: 4px;
    background: var(--accent-light);
    border-radius: 2px;
    margin-bottom: 24px;
}
/* ── Chip / tag ── */
.chip {
    display: inline-block;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--accent-light);
    padding: 6px 18px;
    border: 1.5px solid var(--accent-light);
    border-radius: 40px;
}
/* ── Divider ── */
.divider {
    height: 1px;
    background: rgba(255,255,255,0.10);
    margin: 28px 0;
}
/* ── Number badge (for slide order) ── */
.num-badge {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 800;
    flex-shrink: 0;
}
</style>
</head>
<body>
<div class="canvas">
    @if(isset($slide) && $slide->background_image_path)
        @php
            $bgPath = Storage::disk('public')->path($slide->background_image_path);
            $bgSrc  = file_exists($bgPath)
                ? 'data:' . (mime_content_type($bgPath) ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($bgPath))
                : '';
        @endphp
        @if($bgSrc)
            <img class="bg-image" src="{{ $bgSrc }}" alt="">
            <div class="bg-overlay"></div>
        @endif
    @endif
    <div class="slide-content">
        @yield('body')
    </div>
    <div class="brand-bar">
        <div class="brand-logo">HOME<span>DELVALLE</span></div>
        @if(isset($slide) && isset($totalSlides))
        <div class="slide-counter">{{ $slide->order }} / {{ $totalSlides }}</div>
        @endif
    </div>
</div>
</body>
</html>
