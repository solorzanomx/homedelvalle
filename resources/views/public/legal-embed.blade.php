<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $document->title }}</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: .92rem;
    line-height: 1.75;
    color: #374151;
    padding: 1.25rem 1.5rem 2rem;
    background: #fff;
}
h1 { font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: .5rem; }
.meta { font-size: .75rem; color: #9ca3af; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid #f3f4f6; }
.content h1 { font-size: 1.15rem; font-weight: 700; color: #111827; margin-top: 1.5rem; margin-bottom: .6rem; }
.content h2 { font-size: 1rem; font-weight: 600; color: #1f2937; margin-top: 1.25rem; margin-bottom: .5rem; }
.content h3 { font-size: .92rem; font-weight: 600; color: #1f2937; margin-top: 1rem; margin-bottom: .4rem; }
.content p { margin-bottom: .85rem; }
.content ul, .content ol { margin-bottom: .85rem; padding-left: 1.5rem; }
.content li { margin-bottom: .25rem; }
.content strong { font-weight: 600; color: #1f2937; }
.content a { color: #3B82C4; text-decoration: underline; }
.content table { border-collapse: collapse; width: 100%; margin-bottom: 1rem; }
.content table th, .content table td { border: 1px solid #e5e7eb; padding: .45rem .75rem; font-size: .85rem; }
.content table th { background: #f9fafb; font-weight: 600; }
.content blockquote { border-left: 3px solid #d1d5db; margin: .75rem 0; padding: .6rem .9rem; color: #6b7280; background: #f9fafb; border-radius: 0 6px 6px 0; }
</style>
</head>
<body>
<h1>{{ $document->title }}</h1>
<div class="meta">
    Última actualización: {{ $document->currentVersion?->created_at?->translatedFormat('d \d\e F \d\e Y') ?? $document->updated_at->translatedFormat('d \d\e F \d\e Y') }}
    @if($document->currentVersion)
        &middot; Versión {{ $document->currentVersion->version_number }}
    @endif
</div>
<div class="content">
    @if($document->currentVersion)
        {!! $document->currentVersion->content !!}
    @else
        <p style="color:#9ca3af;text-align:center;padding:2rem 0;">Sin contenido disponible.</p>
    @endif
</div>
</body>
</html>
