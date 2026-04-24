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
    position: absolute; bottom: 22px; right: 28px;
    font-size: 12px; font-weight: 700; letter-spacing: 2.5px;
    color: #94a3b8; text-transform: uppercase;
    font-style: italic; z-index: 2;
}
.logo span { color: #2563A0; }
</style>
</head>
<body>
<div class="canvas">
    <div class="top-bar"></div>

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

    <div class="logo">HOME<span>DELVALLE</span></div>
</div>
</body>
</html>
