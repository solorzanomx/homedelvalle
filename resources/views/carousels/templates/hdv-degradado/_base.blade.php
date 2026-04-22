<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --navy:         #1E3A5F;
    --accent:       #3B82C4;
    --accent-light: #93C5FD;
    --white:        #FFFFFF;
    --gray-1:       #F0F6FF;
    --gray-2:       #CBD5E1;
}
html, body {
    width: 1080px; height: 1350px;
    overflow: hidden;
    font-family: -apple-system, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    color: var(--white);
}
.canvas {
    width: 1080px; height: 1350px;
    background: linear-gradient(145deg, #1E3A5F 0%, #2457A0 45%, #3B82C4 100%);
    display: flex; flex-direction: column;
    overflow: hidden; position: relative;
}
/* Decorative mesh pattern */
.canvas::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
        radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none; z-index: 0;
}
/* Background image */
.canvas .bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
.canvas .bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(145deg, rgba(30,58,95,0.75) 0%, rgba(36,87,160,0.55) 50%, rgba(59,130,196,0.68) 100%);
    z-index: 1;
}
.slide-content {
    flex: 1; position: relative; z-index: 2;
    display: flex; flex-direction: column;
}
/* Brand bar */
.brand-bar {
    flex-shrink: 0; height: 76px;
    background: rgba(12,26,46,0.4);
    backdrop-filter: blur(12px);
    border-top: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 0 56px; z-index: 3;
}
.brand-logo {
    font-size: 20px; font-weight: 700;
    letter-spacing: 4px; color: var(--white);
    text-transform: uppercase; font-style: italic;
}
.brand-logo span { color: var(--accent-light); }
.slide-counter {
    font-size: 16px; font-weight: 500;
    color: rgba(255,255,255,0.55); letter-spacing: 1.5px;
}
/* Elements */
.accent-line {
    width: 48px; height: 4px;
    background: var(--accent-light); border-radius: 2px;
    margin-bottom: 24px;
}
.chip {
    display: inline-block; font-size: 13px;
    font-weight: 700; letter-spacing: 2.5px;
    text-transform: uppercase; color: var(--white);
    padding: 6px 20px;
    border: 1.5px solid rgba(255,255,255,0.5);
    border-radius: 40px; backdrop-filter: blur(4px);
    background: rgba(255,255,255,0.08);
}
.num-badge {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 2px solid rgba(255,255,255,0.4);
    color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 800; flex-shrink: 0;
    backdrop-filter: blur(4px);
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
