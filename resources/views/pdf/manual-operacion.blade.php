@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Manual de Operación — Home del Valle</title>
<style>
{!! $brandCssVars ?? '' !!}
@if($brandFontB64)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $brandFontB64 }}') format('woff2');
}
@endif

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
@page { size: 215.9mm 279.4mm; margin: 14mm 16mm; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 12.5px;
    line-height: 1.65;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* ── Portada ── */
.cover {
    height: 250mm;
    display: flex; flex-direction: column; justify-content: center;
    background: var(--hdv-navy);
    color: #fff;
    padding: 60px;
    border-radius: 6px;
    break-after: page; page-break-after: always;
}
.cover .tag { font-size: 11px; letter-spacing: 3px; text-transform: uppercase; color: rgba(199,210,254,.75); margin-bottom: 18px; }
.cover h1 { font-size: 40px; font-weight: 800; line-height: 1.15; margin-bottom: 16px; }
.cover p  { font-size: 15px; color: rgba(226,232,240,.85); max-width: 420px; }
.cover .fecha { margin-top: 40px; font-size: 12px; color: rgba(199,210,254,.6); }

/* ── Índice ── */
.toc { break-after: page; page-break-after: always; }
.toc h2 { font-size: 22px; font-weight: 800; color: var(--hdv-navy); margin-bottom: 18px; border-bottom: 3px solid var(--hdv-accent); padding-bottom: 8px; }
.toc .toc-cat { font-weight: 700; font-size: 14px; color: var(--hdv-navy); margin: 14px 0 4px; }
.toc .toc-art { font-size: 12.5px; color: #475569; padding-left: 16px; margin: 2px 0; }

/* ── Categorías y artículos ── */
.cat-divider {
    background: var(--hdv-navy); color: #fff;
    padding: 20px 26px; border-radius: 6px; border-left: 6px solid var(--hdv-accent);
    margin-bottom: 22px;
    break-before: page; page-break-before: always;
}
.cat-divider .num { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.cat-divider h2 { font-size: 22px; font-weight: 800; }

.article { margin-bottom: 26px; }
.article + .article { border-top: 1px solid #e2e8f0; padding-top: 22px; }
.article > h1 {
    font-size: 17px; font-weight: 800; color: var(--hdv-navy);
    margin-bottom: 10px;
}
.article h2 { font-size: 13.5px; font-weight: 700; color: var(--hdv-navy); margin: 14px 0 6px; }
.article h3 { font-size: 12.5px; font-weight: 700; color: #334155; margin: 10px 0 4px; }
.article p  { margin: 6px 0; color: #334155; }
.article ul, .article ol { margin: 6px 0 6px 20px; color: #334155; }
.article li { margin: 3px 0; }
.article strong { color: var(--hdv-navy); }
.article table { border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 11.5px; }
.article th { background: #eef2ff; color: var(--hdv-navy); text-align: left; padding: 6px 9px; border: 1px solid #dbe3f0; }
.article td { padding: 6px 9px; border: 1px solid #e2e8f0; }
.article blockquote { border-left: 3px solid var(--hdv-accent); padding: 4px 12px; color: #475569; margin: 8px 0; background: #f8fafc; }
.article code { background: #f1f5f9; border-radius: 3px; padding: 1px 5px; font-size: 11px; }
h1, h2, h3 { break-after: avoid; page-break-after: avoid; }
.article { break-inside: auto; }
</style>
</head>
<body>

{{-- Portada --}}
<div class="cover">
    <div class="tag">Home del Valle · Documento interno</div>
    <h1>Manual de Operación</h1>
    <p>Cómo funciona el negocio y cómo se opera a través del sistema — captación, venta, rentas, leads y portal del cliente.</p>
    <div class="fecha">Generado el {{ now()->translatedFormat('d \d\e F \d\e Y') }} · Contenido vivo: la versión de referencia es el Centro de Ayuda del CRM.</div>
</div>

{{-- Índice --}}
<div class="toc">
    <h2>Contenido</h2>
    @foreach($categories as $i => $cat)
        <div class="toc-cat">{{ $i + 1 }}. {{ $cat->icon }} {{ $cat->name }}</div>
        @foreach($cat->articles as $article)
            <div class="toc-art">{{ $article->title }}</div>
        @endforeach
    @endforeach
</div>

{{-- Contenido --}}
@foreach($categories as $i => $cat)
    <div class="cat-divider">
        <div class="num">Sección {{ $i + 1 }}</div>
        <h2>{{ $cat->icon }} {{ $cat->name }}</h2>
    </div>

    @foreach($cat->articles as $article)
        <div class="article">
            <h1>{{ $article->title }}</h1>
            {!! Illuminate\Support\Str::markdown($article->content) !!}
        </div>
    @endforeach
@endforeach

</body>
</html>
