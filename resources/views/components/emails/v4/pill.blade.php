@props(['color' => 'blue', 'soft' => true])

@php
    if ($color === 'green') {
        $bgColor = $soft ? \App\Mail\V4\Tokens::GREEN_SOFT : \App\Mail\V4\Tokens::GREEN;
        $fgColor = $soft ? \App\Mail\V4\Tokens::GREEN : \App\Mail\V4\Tokens::WHITE;
    } else {
        $bgColor = $soft ? \App\Mail\V4\Tokens::BLUE_SOFT : \App\Mail\V4\Tokens::BLUE;
        $fgColor = $soft ? \App\Mail\V4\Tokens::BLUE : \App\Mail\V4\Tokens::WHITE;
    }
@endphp

<span style="display: inline-block; background-color: {{ $bgColor }}; color: {{ $fgColor }}; padding: {{ \App\Mail\V4\Tokens::SPACE_XS }} 12px; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_FULL }}; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; font-weight: 600;">
    {{ $slot }}
</span>
