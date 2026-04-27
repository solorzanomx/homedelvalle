@props(['padding' => null])

<table role="presentation" width="100%">
    <tr>
        <td @if ($padding) style="padding: {{ $padding }};" @else style="padding: {{ \App\Mail\V4\Tokens::SPACE_2XL }} {{ \App\Mail\V4\Tokens::SPACE_2XL }} {{ \App\Mail\V4\Tokens::SPACE_XL }};" @endif>
            {{ $slot }}
        </td>
    </tr>
</table>
