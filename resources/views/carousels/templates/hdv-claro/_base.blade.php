<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --navy:         #1E3A5F;
    --navy-light:   #2A5080;
    --accent:       #3B82C4;
    --accent-light: #5B9ED6;
    --white:        #FFFFFF;
    --bg:           #F4F7FB;
    --gray-1:       #EEF2F7;
    --gray-2:       #6B7A8E;
    --text:         #1E3A5F;
}
html, body {
    width: 1080px; height: 1350px;
    overflow: hidden;
    font-family: -apple-system, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
}
.canvas {
    width: 1080px; height: 1350px;
    background: var(--bg);
    display: flex; flex-direction: column;
    overflow: hidden; position: relative;
}
/* Background image */
.canvas .bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
.canvas .bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(160deg, rgba(244,247,251,0.88) 0%, rgba(244,247,251,0.72) 50%, rgba(244,247,251,0.92) 100%);
    z-index: 0;
}
/* Top accent stripe */
.canvas::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--navy) 0%, var(--accent) 100%);
    z-index: 3;
}
.slide-content {
    flex: 1; position: relative; z-index: 1;
    display: flex; flex-direction: column;
}
/* Brand bar */
.brand-bar {
    flex-shrink: 0; height: 80px;
    background: var(--navy);
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 0 56px; z-index: 2;
}
.brand-logo {
    font-size: 20px; font-weight: 700;
    letter-spacing: 4px; color: var(--white);
    text-transform: uppercase; font-style: italic;
}
.brand-logo span { color: var(--accent-light); }
.slide-counter {
    font-size: 16px; font-weight: 500;
    color: rgba(255,255,255,0.6); letter-spacing: 1.5px;
}
/* Elements */
.accent-line {
    width: 48px; height: 4px;
    background: var(--accent); border-radius: 2px;
    margin-bottom: 24px;
}
.chip {
    display: inline-block; font-size: 13px;
    font-weight: 700; letter-spacing: 2.5px;
    text-transform: uppercase; color: var(--accent);
    padding: 6px 18px; border: 1.5px solid var(--accent);
    border-radius: 40px;
}
.divider {
    height: 1px; background: rgba(30,58,95,0.10);
    margin: 28px 0;
}
.num-badge {
    width: 52px; height: 52px; border-radius: 50%;
    background: var(--accent); color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 800; flex-shrink: 0;
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
