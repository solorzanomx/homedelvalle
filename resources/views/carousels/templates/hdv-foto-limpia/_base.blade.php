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
    --gray-2:       #CBD5E1;
}
html, body {
    width: 1080px; height: 1350px;
    overflow: hidden;
    font-family: -apple-system, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    background: #111;
    color: var(--white);
}
.canvas {
    width: 1080px; height: 1350px;
    background: #111;
    display: flex; flex-direction: column;
    overflow: hidden; position: relative;
}
/* Background image — FULL visibility */
.canvas .bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
/* Very light overlay — let the photo breathe */
.canvas .bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        180deg,
        rgba(0,0,0,0.15) 0%,
        rgba(0,0,0,0.05) 35%,
        rgba(0,0,0,0.08) 55%,
        rgba(0,0,0,0.70) 100%
    );
    z-index: 1;
}
/* No photo fallback background */
.canvas.no-photo {
    background: linear-gradient(145deg, var(--navy) 0%, #2A5080 100%);
}
.slide-content {
    flex: 1; position: relative; z-index: 2;
    display: flex; flex-direction: column;
    justify-content: flex-end;
    padding-bottom: 0;
}
/* Brand bar */
.brand-bar {
    flex-shrink: 0; height: 80px;
    background: rgba(10,18,30,0.75);
    backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 0 52px; z-index: 3;
}
.brand-logo {
    font-size: 19px; font-weight: 700;
    letter-spacing: 4px; color: var(--white);
    text-transform: uppercase; font-style: italic;
}
.brand-logo span { color: var(--accent-light); }
.slide-counter {
    font-size: 16px; font-weight: 400;
    color: rgba(255,255,255,0.5); letter-spacing: 2px;
}
/* Content panel at bottom */
.content-panel {
    background: rgba(10,18,30,0.72);
    backdrop-filter: blur(12px);
    padding: 40px 56px 44px;
}
/* Elements */
.accent-line {
    width: 44px; height: 3px;
    background: var(--accent-light); border-radius: 2px;
    margin-bottom: 20px;
}
.chip {
    display: inline-block; font-size: 12px;
    font-weight: 700; letter-spacing: 2.5px;
    text-transform: uppercase; color: var(--white);
    padding: 5px 16px;
    background: rgba(59,130,196,0.65);
    border-radius: 40px; margin-bottom: 20px;
}
.num-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--accent); color: var(--white);
    font-size: 20px; font-weight: 800; flex-shrink: 0;
}
</style>
</head>
<body>
<div class="canvas @if(!isset($slide) || !$slide->background_image_path) no-photo @endif">
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
