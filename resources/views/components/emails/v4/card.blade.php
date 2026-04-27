@props(['bg' => null, 'padding' => null, 'accent' => null])

@php
    $bgColor = $bg ?? \App\Mail\V4\Tokens::GRAY_50;
    $cardPadding = $padding ?? \App\Mail\V4\Tokens::SPACE_LG;
    $borderLeftStyle = $accent ? "border-left: 3px solid {$accent};" : '';
@endphp

<table role="presentation" width="100%" style="background-color: {{ $bgColor }}; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_MD }}; {{ $borderLeftStyle }}">
    <tr>
        <td style="padding: {{ $cardPadding }};">
            {{ $slot }}
        </td>
    </tr>
</table>
