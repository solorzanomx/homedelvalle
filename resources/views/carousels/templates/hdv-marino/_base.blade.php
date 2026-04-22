<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --navy:         #0A1628;
    --navy2:        #0F2040;
    --accent:       #3B82C4;
    --accent-light: #60A5FA;
    --white:        #FFFFFF;
    --gray-1:       #E8EDF4;
    --gray-2:       #7A8FA8;
}
html, body {
    width: 1080px; height: 1350px;
    overflow: hidden;
    font-family: -apple-system, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    background: var(--navy);
    color: var(--white);
}
.canvas {
    width: 1080px; height: 1350px;
    background: var(--navy);
    display: flex; flex-direction: column;
    overflow: hidden; position: relative;
}
/* Left blue edge accent */
.canvas::before {
    content: '';
    position: absolute; top: 0; left: 0; bottom: 0;
    width: 8px;
    background: linear-gradient(180deg, var(--accent-light) 0%, var(--accent) 100%);
    z-index: 3;
}
/* Background image */
.canvas .bg-image {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    object-fit: cover; z-index: 0;
}
.canvas .bg-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(160deg, rgba(10,22,40,0.72) 0%, rgba(10,22,40,0.42) 50%, rgba(10,22,40,0.80) 100%);
    z-index: 1;
}
.slide-content {
    flex: 1; position: relative; z-index: 2;
    display: flex; flex-direction: column;
}
/* Brand bar */
.brand-bar {
    flex-shrink: 0; height: 80px;
    background: var(--navy2);
    border-top: 2px solid var(--accent);
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 0 60px 0 68px; z-index: 3;
}
.brand-logo {
    font-size: 21px; font-weight: 800;
    letter-spacing: 5px; color: var(--white);
    text-transform: uppercase; font-style: italic;
}
.brand-logo span { color: var(--accent-light); }
.slide-counter {
    font-size: 16px; font-weight: 500;
    color: rgba(255,255,255,0.5); letter-spacing: 2px;
}
/* Elements */
.accent-line {
    width: 56px; height: 5px;
    background: var(--accent); border-radius: 3px;
    margin-bottom: 28px;
}
.chip {
    display: inline-block; font-size: 12px;
    font-weight: 800; letter-spacing: 3px;
    text-transform: uppercase; color: var(--accent-light);
    padding: 7px 20px; border: 2px solid var(--accent-light);
    border-radius: 4px;
}
.num-badge {
    width: 56px; height: 56px; border-radius: 8px;
    background: var(--accent); color: var(--white);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 900; flex-shrink: 0;
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
