<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Story Preview</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

* { margin:0; padding:0; box-sizing:border-box; }

html, body {
    width: 1080px;
    height: 1920px;
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.story-wrapper {
    position: relative;
    width: 1080px;
    height: 1920px;
    overflow: hidden;
    background: #0C1A2E;
}

/* Background image */
.bg-image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

/* Dark gradient overlay — bottom 55% */
.gradient-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0) 0%,
        rgba(0,0,0,0) 30%,
        rgba(0,0,0,0.15) 50%,
        rgba(0,0,0,0.6) 70%,
        rgba(0,0,0,0.88) 100%
    );
}

/* Top bar with logo */
.top-bar {
    position: absolute;
    top: 80px;
    left: 72px;
    right: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo-wrap {
    display: flex;
    align-items: center;
    gap: 18px;
}

.logo-img {
    height: 72px;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
}

.brand-name {
    font-size: 40px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 12px rgba(0,0,0,0.5);
}

/* Bottom content */
.bottom-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 80px 72px 120px;
}

/* Sticker: location */
.sticker-location {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1.5px solid rgba(255,255,255,0.25);
    border-radius: 60px;
    padding: 14px 32px;
    margin-bottom: 40px;
    font-size: 32px;
    font-weight: 600;
    color: #fff;
    letter-spacing: 0.5px;
}

.sticker-location-icon {
    font-size: 34px;
}

/* Headline */
.headline {
    font-size: 88px;
    font-weight: 900;
    color: #fff;
    line-height: 1.05;
    letter-spacing: -1.5px;
    text-shadow: 0 4px 24px rgba(0,0,0,0.5);
    margin-bottom: 40px;
}

/* Divider line */
.headline-divider {
    width: 80px;
    height: 6px;
    background: #1d4ed8;
    border-radius: 3px;
    margin-bottom: 40px;
}

/* Hashtags */
.hashtags {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 40px;
}

.hashtag-chip {
    font-size: 32px;
    font-weight: 700;
    color: rgba(255,255,255,0.75);
    letter-spacing: 0.3px;
}

/* CTA / link sticker */
.sticker-link {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    background: #1d4ed8;
    border-radius: 60px;
    padding: 16px 40px;
    font-size: 32px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.5px;
}

/* Watermark bar */
.watermark-bar {
    position: absolute;
    bottom: 52px;
    left: 72px;
    right: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.watermark-brand {
    font-size: 30px;
    font-weight: 700;
    color: rgba(255,255,255,0.5);
    letter-spacing: 1px;
    text-transform: uppercase;
}

.watermark-url {
    font-size: 28px;
    font-weight: 500;
    color: rgba(255,255,255,0.4);
}
</style>
</head>
<body>
<div class="story-wrapper">

    {{-- Background --}}
    @if($story->background_image_path)
    <img class="bg-image" src="{{ Storage::disk('public')->path($story->background_image_path) }}" alt="">
    @else
    <div style="position:absolute;inset:0;background:linear-gradient(135deg,#0C1A2E 0%,#1d4ed8 60%,#0C1A2E 100%);"></div>
    @endif

    {{-- Gradient overlay --}}
    <div class="gradient-overlay"></div>

    {{-- Top bar --}}
    @php
        $settings  = \App\Models\SiteSetting::first();
        $logoPath  = $settings?->logo_path_dark ?? $settings?->logo_path;
        $logoSrc   = null;
        if ($logoPath && \Storage::disk('public')->exists($logoPath)) {
            $abs   = \Storage::disk('public')->path($logoPath);
            $mime  = mime_content_type($abs) ?: 'image/png';
            $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
        }
        $siteName = $settings?->site_name ?? 'Home del Valle';
        $siteUrl  = parse_url(config('app.url'), PHP_URL_HOST) ?? 'homedelvalle.mx';
    @endphp
    <div class="top-bar">
        <div class="logo-wrap">
            @if($logoSrc)
            <img class="logo-img" src="{{ $logoSrc }}" alt="{{ $siteName }}">
            @else
            <div class="brand-name">{{ $siteName }}</div>
            @endif
        </div>
    </div>

    {{-- Bottom content --}}
    <div class="bottom-content">
        {{-- Location sticker --}}
        @if($story->sticker_location)
        <div>
            <span class="sticker-location">
                <span class="sticker-location-icon">📍</span>
                {{ $story->sticker_location }}
            </span>
        </div>
        @endif

        {{-- Headline --}}
        @if($story->headline)
        <h1 class="headline">{{ $story->headline }}</h1>
        <div class="headline-divider"></div>
        @endif

        {{-- Hashtags --}}
        @if(!empty($story->sticker_hashtags))
        <div class="hashtags">
            @foreach($story->sticker_hashtags as $tag)
            <span class="hashtag-chip">#{{ ltrim($tag, '#') }}</span>
            @endforeach
        </div>
        @endif

        {{-- Link sticker --}}
        @if($story->sticker_link)
        @php
            $displayLink = preg_replace('#^https?://(www\.)?#i', '', $story->sticker_link);
            $displayLink = rtrim($displayLink, '/');
        @endphp
        <div>
            <span class="sticker-link">&#128279; {{ $displayLink }}</span>
        </div>
        @endif
    </div>

    {{-- Watermark --}}
    <div class="watermark-bar">
        <span class="watermark-brand">{{ $siteName }}</span>
        <span class="watermark-url">{{ $siteUrl }}</span>
    </div>

</div>
</body>
</html>
