@php
    $siteName = $siteName ?? 'Home del Valle';
    $mode = $mode ?? 'pdf';
    $primary = $property->primaryPhoto();
    $photoPath = $primary ? $primary->path : $property->photo;
    $broker = $property->broker;

    $typeLabels = ['House'=>'Casa','Apartment'=>'Departamento','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega','Building'=>'Edificio'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
    $statusLabels = ['available'=>'Disponible','sold'=>'Vendido','rented'=>'Rentado','reserved'=>'Reservado'];

    if ($mode === 'email' && $photoPath) {
        $photoSrc = asset('storage/' . $photoPath);
    } elseif ($photoPath) {
        $fullPath = storage_path('app/public/' . $photoPath);
        if (file_exists($fullPath)) {
            $mime = mime_content_type($fullPath);
            $photoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
        } else {
            $photoSrc = null;
        }
    } else {
        $photoSrc = null;
    }

    $amenityLabels = [
        'pool' => 'Alberca', 'gym' => 'Gimnasio', 'garden' => 'Jardin', 'terrace' => 'Terraza',
        'security' => 'Seguridad 24/7', 'elevator' => 'Elevador', 'rooftop' => 'Rooftop',
        'bbq' => 'Area de BBQ', 'playground' => 'Area de juegos', 'pet_friendly' => 'Pet Friendly',
        'laundry' => 'Lavanderia', 'storage' => 'Bodega', 'concierge' => 'Concierge',
        'business_center' => 'Business Center', 'cinema' => 'Cine', 'spa' => 'Spa',
        'jacuzzi' => 'Jacuzzi', 'sauna' => 'Sauna', 'tennis' => 'Cancha de Tenis',
        'paddle' => 'Cancha de Padel', 'co_working' => 'Co-working',
    ];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>{{ $property->title }} — {{ $siteName }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; color: #333; background: #fff; font-size: 13px; line-height: 1.5; }
    .ficha-wrap { max-width: 680px; margin: 0 auto; background: #fff; }
    .ficha-header { background: linear-gradient(135deg, #667eea, #764ba2); padding: 20px 28px; color: #fff; }
    .ficha-header h1 { font-size: 18px; font-weight: 700; margin: 0; }
    .ficha-header p { font-size: 11px; opacity: 0.8; margin: 2px 0 0; }
    .ficha-photo { width: 100%; max-height: 360px; object-fit: cover; display: block; }
    .ficha-body { padding: 24px 28px; }
    .ficha-title { font-size: 17px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
    .ficha-price { font-size: 22px; font-weight: 800; color: #667eea; margin: 0 0 6px; }
    .ficha-location { font-size: 12px; color: #64748b; margin: 0 0 16px; }
    .ficha-badges { margin: 0 0 16px; }
    .ficha-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; margin: 0 4px 4px 0; }
    .badge-type { background: #f1f5f9; color: #475569; }
    .badge-op { background: #667eea; color: #fff; }
    .badge-status { background: #dcfce7; color: #166534; }
    .ficha-specs { width: 100%; border-collapse: collapse; margin: 0 0 20px; }
    .ficha-specs td { padding: 8px 12px; border: 1px solid #e2e8f0; font-size: 12px; width: 50%; }
    .ficha-specs .spec-label { color: #64748b; font-weight: 400; }
    .ficha-specs .spec-value { font-weight: 700; color: #1e293b; }
    .ficha-section { font-size: 14px; font-weight: 700; color: #1e293b; margin: 20px 0 8px; padding-bottom: 4px; border-bottom: 2px solid #667eea; }
    .ficha-desc { font-size: 12px; color: #475569; line-height: 1.7; white-space: pre-line; }
    .ficha-amenities { margin: 0; padding: 0; list-style: none; }
    .ficha-amenities li { display: inline-block; background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 4px; font-size: 11px; margin: 0 4px 6px 0; }
    .ficha-footer { background: #f8fafc; padding: 20px 28px; border-top: 1px solid #e2e8f0; }
    .ficha-broker { font-size: 12px; color: #475569; }
    .ficha-broker strong { color: #1e293b; font-size: 13px; }
    .ficha-brand { margin-top: 12px; font-size: 11px; color: #94a3b8; }
    .ficha-gallery { padding: 0 28px 8px; }
    .ficha-gallery-grid { width: 100%; border-collapse: collapse; }
    .ficha-gallery-grid td { padding: 3px; width: 25%; }
    .ficha-gallery-grid img { width: 100%; height: 100px; object-fit: cover; border-radius: 4px; display: block; }
</style>
</head>
<body>
<div class="ficha-wrap">
    {{-- Header --}}
    <div class="ficha-header">
        <h1>{{ $siteName }}</h1>
        <p>Ficha de Propiedad</p>
    </div>

    {{-- Main Photo --}}
    @if($photoSrc)
    <img class="ficha-photo" src="{{ $photoSrc }}" alt="{{ $property->title }}">
    @endif

    {{-- Body --}}
    <div class="ficha-body">
        <p class="ficha-price">{{ $property->formatted_price }}</p>
        <h2 class="ficha-title">{{ $property->title }}</h2>
        <p class="ficha-location">
            {{ implode(', ', array_filter([$property->colony, $property->city, $property->zipcode ? 'C.P. ' . $property->zipcode : null])) }}
            @if($property->address)
            <br>{{ $property->address }}
            @endif
        </p>

        {{-- Badges --}}
        <div class="ficha-badges">
            @if($property->property_type)
            <span class="ficha-badge badge-type">{{ $typeLabels[$property->property_type] ?? $property->property_type }}</span>
            @endif
            @if($property->operation_type)
            <span class="ficha-badge badge-op">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
            @endif
            @if($property->status)
            <span class="ficha-badge badge-status">{{ $statusLabels[$property->status] ?? $property->status }}</span>
            @endif
        </div>

        {{-- Specs Table --}}
        <table class="ficha-specs">
            @if($property->bedrooms)
            <tr>
                <td><span class="spec-label">Recamaras</span></td>
                <td><span class="spec-value">{{ $property->bedrooms }}</span></td>
            </tr>
            @endif
            @if($property->bathrooms)
            <tr>
                <td><span class="spec-label">Banos</span></td>
                <td><span class="spec-value">{{ $property->bathrooms }}{{ $property->half_bathrooms ? ' + ' . $property->half_bathrooms . ' medio' : '' }}</span></td>
            </tr>
            @endif
            @if($property->area || $property->construction_area || $property->lot_area)
            <tr>
                <td><span class="spec-label">Superficie</span></td>
                <td><span class="spec-value">
                    @if($property->lot_area) Terreno: {{ number_format($property->lot_area) }} m² @endif
                    @if($property->construction_area) {{ $property->lot_area ? ' / ' : '' }}Const: {{ number_format($property->construction_area) }} m² @endif
                    @if($property->area && !$property->lot_area && !$property->construction_area) {{ number_format($property->area) }} m² @endif
                </span></td>
            </tr>
            @endif
            @if($property->parking)
            <tr>
                <td><span class="spec-label">Estacionamientos</span></td>
                <td><span class="spec-value">{{ $property->parking }}</span></td>
            </tr>
            @endif
            @if($property->floors)
            <tr>
                <td><span class="spec-label">Pisos</span></td>
                <td><span class="spec-value">{{ $property->floors }}</span></td>
            </tr>
            @endif
            @if($property->year_built)
            <tr>
                <td><span class="spec-label">Ano de construccion</span></td>
                <td><span class="spec-value">{{ $property->year_built }}</span></td>
            </tr>
            @endif
            @if($property->maintenance_fee && $property->maintenance_fee > 0)
            <tr>
                <td><span class="spec-label">Mantenimiento</span></td>
                <td><span class="spec-value">${{ number_format($property->maintenance_fee, 0) }}/mes</span></td>
            </tr>
            @endif
            @if($property->furnished)
            <tr>
                <td><span class="spec-label">Amueblado</span></td>
                <td><span class="spec-value">Si</span></td>
            </tr>
            @endif
        </table>

        {{-- Description --}}
        @if($property->description)
        <div class="ficha-section">Descripcion</div>
        <p class="ficha-desc">{{ Str::limit(strip_tags($property->description), 800) }}</p>
        @endif

        {{-- Amenities --}}
        @if($property->amenities && count($property->amenities))
        <div class="ficha-section">Amenidades</div>
        <ul class="ficha-amenities">
            @foreach($property->amenities as $a)
            <li>{{ $amenityLabels[$a] ?? $a }}</li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- Secondary Photos (PDF only, up to 8) --}}
    @if($mode === 'pdf' && $property->photos->count() > 1)
    <div class="ficha-gallery">
        <div class="ficha-section" style="margin: 0 0 8px;">Galeria</div>
        <table class="ficha-gallery-grid">
            @foreach($property->photos->reject(fn($p) => $p->is_primary)->take(8)->chunk(4) as $row)
            <tr>
                @foreach($row as $gPhoto)
                @php
                    $gPath = storage_path('app/public/' . $gPhoto->path);
                    $gSrc = file_exists($gPath) ? 'data:' . mime_content_type($gPath) . ';base64,' . base64_encode(file_get_contents($gPath)) : null;
                @endphp
                @if($gSrc)
                <td><img src="{{ $gSrc }}" alt=""></td>
                @endif
                @endforeach
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="ficha-footer">
        @if($broker)
        <div class="ficha-broker">
            <strong>{{ $broker->name }}</strong><br>
            @if($broker->phone) Tel: {{ $broker->phone }}<br> @endif
            @if($broker->email) {{ $broker->email }} @endif
        </div>
        @endif
        <p class="ficha-brand">{{ $siteName }} — homedelvalle.mx</p>
    </div>
</div>
</body>
</html>
