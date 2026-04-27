@props(['tag' => null, 'tagColor' => 'blue'])

<table role="presentation" width="100%">
    <tr>
        <td style="border-bottom: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; padding: {{ \App\Mail\V4\Tokens::SPACE_LG }};">
            <table role="presentation" width="100%">
                <tr>
                    <td align="left" style="vertical-align: middle;">
                        <div style="display: inline-flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background-color: {{ \App\Mail\V4\Tokens::NAVY }}; color: {{ \App\Mail\V4\Tokens::WHITE }}; font-size: 14px; font-weight: 600; line-height: 32px; text-align: center;">HV</div>
                            <div>
                                <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">Home del Valle</p>
                                <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }};">homedelvalle.mx</p>
                            </div>
                        </div>
                    </td>
                    @if ($tag)
                        <td align="right" style="vertical-align: middle;">
                            @if ($tagColor === 'green')
                                <div style="display: inline-block; background-color: {{ \App\Mail\V4\Tokens::GREEN_SOFT }}; color: {{ \App\Mail\V4\Tokens::GREEN }}; padding: {{ \App\Mail\V4\Tokens::SPACE_XS }} 12px; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_FULL }}; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; font-weight: 600;">{{ $tag }}</div>
                            @else
                                <div style="display: inline-block; background-color: {{ \App\Mail\V4\Tokens::BLUE_SOFT }}; color: {{ \App\Mail\V4\Tokens::BLUE }}; padding: {{ \App\Mail\V4\Tokens::SPACE_XS }} 12px; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_FULL }}; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; font-weight: 600;">{{ $tag }}</div>
                            @endif
                        </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>
</table>
