@extends('layouts.public')

@section('meta')
<title>Observatorio de precios inmobiliarios en Benito Juárez, CDMX | Home del Valle</title>
<meta name="description" content="Consulta los precios actuales por m² en las principales colonias de Benito Juárez. Datos actualizados mensualmente por Home del Valle.">
<link rel="canonical" href="{{ url('/mercado') }}">
@endsection

@section('content')

{{-- Hero --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:4rem 1.5rem 3rem;">
    <div style="max-width:900px;margin:0 auto;text-align:center;">
        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:2px;color:rgba(255,255,255,.5);margin-bottom:.75rem;">
            Benito Juárez · Ciudad de México
        </div>
        <h1 style="font-size:clamp(1.75rem,4vw,2.75rem);font-weight:700;line-height:1.2;margin-bottom:1rem;">
            Observatorio de precios<br>inmobiliarios
        </h1>
        <p style="font-size:1rem;color:rgba(255,255,255,.7);max-width:560px;margin:0 auto 2rem;line-height:1.6;">
            Referencias de mercado actualizadas mensualmente para las principales zonas de Benito Juárez.
            No son avalúos formales, son datos de mercado para orientarte.
        </p>
        <a href="{{ route('mercado.opinion') }}"
           style="display:inline-block;background:#2563eb;color:#fff;padding:.75rem 1.75rem;border-radius:8px;font-weight:600;font-size:.9rem;text-decoration:none;">
            Solicitar opinión de valor personalizada →
        </a>
    </div>
</section>

{{-- Zonas --}}
<section style="max-width:960px;margin:0 auto;padding:3rem 1.5rem;">
    <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:.4rem;">Zonas de Benito Juárez</h2>
    <p style="color:#6b7280;font-size:.9rem;margin-bottom:2rem;">
        Selecciona una zona para ver los precios por colonia, tipo de inmueble y antigüedad.
    </p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1.25rem;">
        @foreach($zones as $zone)
        <a href="{{ route('mercado.zone', $zone->slug) }}"
           style="display:block;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1.5rem;text-decoration:none;transition:box-shadow .15s,transform .15s;"
           onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.boxShadow='';this.style.transform=''">

            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#111827;">{{ $zone->name }}</h3>
                @if($zone->avg_price_m2)
                <span style="font-size:.75rem;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:20px;padding:.15rem .6rem;font-weight:600;white-space:nowrap;">
                    ~${{ number_format($zone->avg_price_m2) }}/m²
                </span>
                @endif
            </div>

            <p style="font-size:.83rem;color:#6b7280;margin-bottom:1rem;line-height:1.5;">
                {{ $zone->short_description }}
            </p>

            <div style="font-size:.78rem;color:#9ca3af;">
                {{ $zone->publishedColonias->count() }} colonias
                <span style="float:right;color:#2563eb;font-weight:500;">Ver precios →</span>
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- Disclaimer --}}
<section style="background:#f8fafc;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;padding:1.5rem;">
    <div style="max-width:800px;margin:0 auto;font-size:.8rem;color:#9ca3af;text-align:center;line-height:1.6;">
        <strong style="color:#6b7280;">Nota metodológica:</strong>
        Los precios son referencias de mercado basadas en oferta publicada y transacciones recientes. Actualizados mensualmente.
        No constituyen un avalúo formal. Para una opinión de valor precisa sobre tu inmueble,
        <a href="{{ route('mercado.opinion') }}" style="color:#2563eb;">contáctanos</a>.
    </div>
</section>

{{-- CTA final --}}
<section style="padding:3.5rem 1.5rem;text-align:center;">
    <div style="max-width:600px;margin:0 auto;">
        <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:.75rem;">
            ¿Cuánto vale tu inmueble en Benito Juárez?
        </h2>
        <p style="color:#6b7280;margin-bottom:1.5rem;line-height:1.6;">
            En Home del Valle preparamos una opinión de valor personalizada con análisis de mercado,
            ajustes por las características de tu inmueble y un precio sugerido de salida.
        </p>
        <a href="{{ route('mercado.opinion') }}"
           style="display:inline-block;background:#111827;color:#fff;padding:.8rem 2rem;border-radius:8px;font-weight:600;font-size:.9rem;text-decoration:none;">
            Quiero mi opinión de valor →
        </a>
    </div>
</section>

@endsection
