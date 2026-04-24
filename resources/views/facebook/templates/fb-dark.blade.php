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
/* Decorative dots top-right */
.canvas::before {
    content: '';
    position: absolute; top: 0; right: 0;
    width: 340px; height: 340px;
    background-image: radial-gradient(circle, rgba(37,99,160,0.25) 1.5px, transparent 1.5px);
    background-size: 22px 22px;
    pointer-events: none;
}
/* Accent glow bottom-left */
.canvas::after {
    content: '';
    position: absolute; bottom: -100px; left: -80px;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37,99,160,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.headline {
    font-size: 56px; font-weight: 800; line-height: 1.1;
    letter-spacing: -1.5px; color: #fff;
    max-width: 680px;
    margin-bottom: 20px;
    position: relative; z-index: 1;
}
.accent-line {
    width: 60px; height: 4px;
    background: #3B82F6; border-radius: 2px;
    margin-bottom: 20px;
    position: relative; z-index: 1;
}
.subheadline {
    font-size: 24px; font-weight: 400; line-height: 1.5;
    color: #8C9AB0; max-width: 600px;
    position: relative; z-index: 1;
}
.body-text {
    font-size: 18px; font-weight: 400; line-height: 1.6;
    color: rgba(255,255,255,0.6); max-width: 580px;
    margin-top: 16px;
    position: relative; z-index: 1;
}
/* Logo bottom-right */
.logo {
    position: absolute; bottom: 24px; right: 32px;
    font-size: 14px; font-weight: 700; letter-spacing: 2.5px;
    color: rgba(255,255,255,0.7); text-transform: uppercase;
    font-style: italic; z-index: 2;
}
.logo span { color: #3B82F6; }
/* Vertical blue stripe left */
.stripe {
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 6px; background: #2563A0;
}
</style>
</head>
<body>
<div class="canvas">
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

    <div class="logo">HOME<span>DELVALLE</span></div>
</div>
</body>
</html>
