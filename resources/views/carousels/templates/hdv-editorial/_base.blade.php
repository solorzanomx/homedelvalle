<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --black:        #0A0A0A;
    --navy:         #1E3A5F;
    --accent:       #3B82C4;
    --accent-light: #3B82C4;
    --white:        #FFFFFF;
    --gray-1:       #F5F5F5;
    --gray-2:       #888888;
    --border:       rgba(0,0,0,0.08);
}
html, body {
    width: 1080px; height: 1350px;
    overflow: hidden;
    font-family: 'Georgia', 'Times New Roman', serif;
    background: var(--white);
    color: var(--black);
}
.canvas {
    width: 1080px; height: 1350px;
    background: var(--white);
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
    background: linear-gradient(160deg, rgba(255,255,255,0.88) 0%, rgba(255,255,255,0.72) 50%, rgba(255,255,255,0.90) 100%);
    z-index: 1;
}
.slide-content {
    flex: 1; position: relative; z-index: 2;
    display: flex; flex-direction: column;
}
/* Top editorial header */
.editorial-header {
    padding: 36px 64px 0;
    display: flex; align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid var(--black);
    padding-bottom: 20px;
}
.editorial-logo {
    font-size: 14px; font-weight: 700;
    letter-spacing: 5px; color: var(--black);
    text-transform: uppercase; font-style: normal;
    font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
}
.editorial-logo span { color: var(--accent); }
.editorial-issue {
    font-size: 13px; color: var(--gray-2);
    letter-spacing: 1px; font-family: -apple-system, Arial, sans-serif;
}
/* Brand bar */
.brand-bar {
    flex-shrink: 0; height: 72px;
    background: var(--black);
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 0 56px; z-index: 3;
}
.brand-logo {
    font-size: 18px; font-weight: 700;
    letter-spacing: 4px; color: var(--white);
    text-transform: uppercase; font-style: italic;
    font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
}
.brand-logo span { color: var(--accent); }
.slide-counter {
    font-size: 15px; font-weight: 400;
    color: rgba(255,255,255,0.5); letter-spacing: 2px;
    font-family: -apple-system, Arial, sans-serif;
}
/* Elements */
.accent-line {
    width: 56px; height: 3px;
    background: var(--accent); border-radius: 0;
    margin-bottom: 28px;
}
.chip {
    display: inline-block; font-size: 11px;
    font-weight: 700; letter-spacing: 3px;
    text-transform: uppercase; color: var(--accent);
    font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
}
.num-badge {
    width: 52px; height: 52px;
    background: var(--black); color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 900; flex-shrink: 0;
    font-family: -apple-system, Arial, sans-serif;
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
        <div class="editorial-header">
            <div class="editorial-logo">HOME<span>DELVALLE</span></div>
            <div class="editorial-issue" style="font-family:-apple-system,Arial,sans-serif;">
                @if(isset($slide) && isset($totalSlides))
                    {{ $slide->order }} · {{ $totalSlides }}
                @endif
            </div>
        </div>
        @yield('body')
    </div>
    <div class="brand-bar">
        <div class="brand-logo">HOME<span>DELVALLE</span></div>
        <div class="slide-counter">Inmobiliaria · Baja California</div>
    </div>
</div>
</body>
</html>
