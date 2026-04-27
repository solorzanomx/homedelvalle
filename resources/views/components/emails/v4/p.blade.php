@props(['size' => '15', 'color' => 'text', 'mb' => null])

@php
    $fontSizeMap = [
        '12' => \App\Mail\V4\Tokens::FONT_SIZE_12,
        '13' => \App\Mail\V4\Tokens::FONT_SIZE_13,
        '15' => \App\Mail\V4\Tokens::FONT_SIZE_15,
    ];

    $colorMap = [
        'text' => \App\Mail\V4\Tokens::TEXT,
        'muted' => \App\Mail\V4\Tokens::MUTED,
        'subtle' => \App\Mail\V4\Tokens::SUBTLE,
        'ink' => \App\Mail\V4\Tokens::INK,
    ];

    $fontSize = $fontSizeMap[$size] ?? \App\Mail\V4\Tokens::FONT_SIZE_15;
    $textColor = $colorMap[$color] ?? \App\Mail\V4\Tokens::TEXT;
    $marginBottom = $mb ?? \App\Mail\V4\Tokens::SPACE_MD;
@endphp

<p style="margin: 0 0 {{ $marginBottom }} 0; font-size: {{ $fontSize }}; color: {{ $textColor }}; line-height: {{ \App\Mail\V4\Tokens::LINE_HEIGHT_1_6 }};">
    {{ $slot }}
</p>
