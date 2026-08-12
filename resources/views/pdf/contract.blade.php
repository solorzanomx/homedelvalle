@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $contract->title }}</title>
<style>
@include('pdf._contract_style')
</style>
</head>
<body>

@php $caratulaClause = $clauses->where('section', 'caratula')->first(); @endphp
<div class="caratula-block">
    <div>
        <div class="doc-title">{{ $isSale ? 'Contrato de Promesa de Compraventa' : 'Contrato de Arrendamiento' }}</div>
        <div class="doc-folio">Folio {{ $folio }} &middot; {{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
        @if($caratulaClause)
            {!! $caratulaClause['body'] !!}
        @else
        <table class="caratula-table">
            <tr><td>{{ $isSale ? 'Promitente Vendedor' : 'Arrendador' }}</td><td>{{ $vendedorNombre }}</td></tr>
            <tr><td>{{ $isSale ? 'Promitente Compradora' : 'Arrendataria' }}</td><td>{{ $compradorNombre }}</td></tr>
            @if(!empty($propiedadDireccion))
            <tr><td>Inmueble</td><td>{{ $propiedadDireccion }}</td></tr>
            @endif
            @if(!empty($precioTexto))
            <tr><td>{{ $isSale ? 'Precio' : 'Renta mensual' }}</td><td>{{ $precioTexto }}</td></tr>
            @endif
            @if($isSale && !empty($plazoEscrituracion))
            <tr><td>Plazo para escriturar</td><td>{{ $plazoEscrituracion }}</td></tr>
            @endif
        </table>
        <p>{{ $contract->title }} que celebran, por una parte, {{ $vendedorNombre }}, por su propio derecho, a quien en lo sucesivo se le denominará &ldquo;{{ $vendedorLabel }}&rdquo;; y por la otra parte, {{ $compradorNombre }}, por su propio derecho, a quien en lo sucesivo se le denominará &ldquo;{{ $compradorLabel }}&rdquo;; y a ambos conjuntamente como &ldquo;LAS PARTES&rdquo;, quienes manifiestan su voluntad de obligarse y sujetan el presente contrato al tenor de las declaraciones y cláusulas contenidas en las páginas siguientes.</p>
        @endif
    </div>
    <div class="closing-note">Este documento forma parte del expediente digital de la operación <strong>{{ $folio }}</strong> en Home del Valle Bienes Raíces.</div>
</div>

@php $declaraciones = $clauses->where('section', 'declaracion'); @endphp
@if($declaraciones->isNotEmpty())
<div class="section-title first">Declaraciones</div>
@foreach($declaraciones as $c)
<div class="clause-block"><span class="ctitle">{{ $c['title'] }}</span>{!! $c['body'] !!}</div>
@endforeach
@endif

@php $clausulas = $clauses->where('section', 'clausula'); @endphp
@if($clausulas->isNotEmpty())
<div class="section-title">Cláusulas</div>
@foreach($clausulas as $c)
<div class="clause-block"><span class="ctitle">{{ $c['title'] }}</span>{!! $c['body'] !!}</div>
@endforeach
@endif

@php $firma = $clauses->where('section', 'firma')->first(); @endphp
<div class="firma-block">
@if($firma)
<div class="clause-block"><span class="ctitle">{{ $firma['title'] }}</span>{!! $firma['body'] !!}</div>
@endif
<div class="sign-row">
    <div class="sign-col">
        <div class="sign-heading">{{ $vendedorLabel }}</div>
        <div class="sign-line"><div class="sign-name">{{ $vendedorNombre }}</div></div>
    </div>
    <div class="sign-col">
        <div class="sign-heading">{{ $compradorLabel }}</div>
        <div class="sign-line"><div class="sign-name">{{ $compradorNombre }}</div></div>
    </div>
</div>
</div>

</body>
</html>
