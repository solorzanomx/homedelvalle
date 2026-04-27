@props(['href', 'variant' => 'primary', 'size' => 'md'])

@php
    $variantConfig = [
        'primary' => [
            'bg' => \App\Mail\V4\Tokens::NAVY,
            'fg' => \App\Mail\V4\Tokens::WHITE,
        ],
        'blue' => [
            'bg' => \App\Mail\V4\Tokens::BLUE,
            'fg' => \App\Mail\V4\Tokens::WHITE,
        ],
        'secondary' => [
            'bg' => \App\Mail\V4\Tokens::WHITE,
            'fg' => \App\Mail\V4\Tokens::NAVY,
            'border' => \App\Mail\V4\Tokens::NAVY,
        ],
        'ghost' => [
            'bg' => 'transparent',
            'fg' => \App\Mail\V4\Tokens::BLUE,
        ],
    ];

    $sizeConfig = [
        'md' => ['padding' => '12px 24px', 'fontSize' => \App\Mail\V4\Tokens::FONT_SIZE_14],
        'sm' => ['padding' => '10px 18px', 'fontSize' => \App\Mail\V4\Tokens::FONT_SIZE_12],
    ];

    $config = $variantConfig[$variant] ?? $variantConfig['primary'];
    $sizeConf = $sizeConfig[$size] ?? $sizeConfig['md'];
    $borderStyle = isset($config['border']) ? "border: 1px solid {$config['border']}; border-radius: 8px;" : '';
@endphp

<table role="presentation" style="margin: 0; border-collapse: collapse;">
    <tr>
        <td align="center" style="border-radius: {{ \App\Mail\V4\Tokens::RADIUS_MD }}; background-color: {{ $config['bg'] }}; {{ $borderStyle }}">
            <a href="{{ $href }}" class="button-link" style="display: inline-block; padding: {{ $sizeConf['padding'] }}; color: {{ $config['fg'] }}; text-decoration: none; font-size: {{ $sizeConf['fontSize'] }}; font-weight: 600; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_MD }};">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
