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
    background: #1e3a8a;
    color: #fff;
}
.canvas {
    width: 1200px; height: 628px;
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 45%, #2563eb 70%, #3b82f6 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px 80px;
    text-align: center;
}
/* Top-left circle glow */
.canvas::before {
    content: '';
    position: absolute; top: -120px; left: -120px;
    width: 480px; height: 480px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    pointer-events: none;
}
/* Bottom-right circle glow */
.canvas::after {
    content: '';
    position: absolute; bottom: -80px; right: -80px;
    width: 360px; height: 360px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(30,58,138,0.5) 0%, transparent 70%);
    pointer-events: none;
}
/* Dot grid overlay */
.dots {
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,0.08) 1.5px, transparent 1.5px);
    background-size: 28px 28px;
    pointer-events: none;
}
.tag {
    font-size: 12px; font-weight: 700; letter-spacing: 3px;
    text-transform: uppercase; color: rgba(255,255,255,0.65);
    margin-bottom: 22px;
    position: relative; z-index: 1;
    border: 1px solid rgba(255,255,255,0.3);
    display: inline-block; padding: 5px 16px; border-radius: 40px;
}
.headline {
    font-size: 54px; font-weight: 800; line-height: 1.08;
    letter-spacing: -1.5px; color: #fff;
    max-width: 820px;
    margin-bottom: 24px;
    position: relative; z-index: 1;
}
.accent-line {
    width: 60px; height: 4px;
    background: rgba(255,255,255,0.5); border-radius: 2px;
    margin: 0 auto 22px;
    position: relative; z-index: 1;
}
.subheadline {
    font-size: 22px; font-weight: 400; line-height: 1.5;
    color: rgba(255,255,255,0.82); max-width: 700px;
    position: relative; z-index: 1;
}
.body-text {
    font-size: 17px; font-weight: 400; line-height: 1.6;
    color: rgba(255,255,255,0.6); max-width: 620px;
    margin-top: 14px;
    position: relative; z-index: 1;
}
/* Logo bottom-right */
.logo {
    position: absolute; bottom: 20px; right: 28px;
    height: 36px; width: auto;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.85;
    z-index: 2;
}
</style>
</head>
<body>
<div class="canvas">
    <div class="dots"></div>

    <div class="tag">Home del Valle · CDMX</div>

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

    @if($logoSrc ?? null)
    <img class="logo" src="{{ $logoSrc }}" alt="Home del Valle">
    @endif
</div>
</body>
</html>
