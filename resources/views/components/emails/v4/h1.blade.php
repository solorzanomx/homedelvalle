@props(['mb' => null])

<h1 @if ($mb) style="margin: 0 0 {{ $mb }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_24 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }}; letter-spacing: -0.01em; line-height: 1.3;" @else style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_MD }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_24 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }}; letter-spacing: -0.01em; line-height: 1.3;" @endif>
    {{ $slot }}
</h1>
