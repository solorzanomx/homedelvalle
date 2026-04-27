@props(['label', 'last' => false])

<table role="presentation" width="100%" style="border-collapse: collapse; @unless ($last) border-bottom: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; @endunless padding: {{ \App\Mail\V4\Tokens::SPACE_MD }} 0;">
    <tr>
        <td width="96" style="padding: 0; vertical-align: top; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }}; font-weight: 500;">{{ $label }}</td>
        <td style="padding: 0 0 0 {{ \App\Mail\V4\Tokens::SPACE_LG }}; vertical-align: top; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; color: {{ \App\Mail\V4\Tokens::INK }};">
            {{ $slot }}
        </td>
    </tr>
</table>
