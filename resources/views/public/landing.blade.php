@extends('layouts.landing')

@section('meta')
<title>{{ $meta['title'] ?? 'Vende tu propiedad en Colonia del Valle | Home del Valle' }}</title>
<meta name="description" content="{{ $meta['description'] ?? 'Vende tu departamento en la Colonia del Valle rápido y al mejor precio. Asesoría profesional, clientes calificados y cierre seguro.' }}">
<meta name="keywords" content="{{ $meta['keywords'] ?? 'vender departamento colonia del valle, inmobiliaria cdmx, venta propiedades benito juárez, asesor inmobiliario del valle' }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url()->current() }}">

{{-- Open Graph --}}
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $meta['title'] ?? 'Vende tu propiedad en Colonia del Valle | Home del Valle' }}">
<meta property="og:description" content="{{ $meta['description'] ?? 'Vende tu departamento rápido y al mejor precio con asesoría profesional.' }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:locale" content="es_MX">

{{-- JSON-LD --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "RealEstateAgent",
    "name": "{{ $siteSettings?->site_name ?? 'Home del Valle' }}",
    "description": "{{ $meta['description'] ?? 'Inmobiliaria especializada en Colonia del Valle, CDMX' }}",
    "url": "{{ url('/') }}",
    "areaServed": {
        "@@type": "Place",
        "name": "Colonia del Valle, Ciudad de México"
    },
    "address": {
        "@@type": "PostalAddress",
        "addressLocality": "Ciudad de México",
        "addressRegion": "CDMX",
        "addressCountry": "MX"
    }
    @if($siteSettings?->contact_phone)
    ,"telephone": "{{ $siteSettings->contact_phone }}"
    @endif
}
</script>

{{-- FAQ Schema --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($faqs as $i => $faq)
        {
            "@@type": "Question",
            "name": "{{ $faq['q'] }}",
            "acceptedAnswer": { "@@type": "Answer", "text": "{{ $faq['a'] }}" }
        }@if($i < count($faqs) - 1),@endif

        @endforeach
    ]
}
</script>
@endsection

@section('content')

{{-- ======================================================
     1. HERO — Formulario de captación prominente
     ====================================================== --}}
<section class="relative bg-gray-950 overflow-hidden">
    {{-- Background pattern --}}
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><g fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;><g fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;><path d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/></g></g></svg>');"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Copy --}}
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm text-gray-300 mb-6">
                    <span class="h-2 w-2 rounded-full bg-green-400 animate-pulse"></span>
                    {{ $campaign['badge'] ?? 'Asesoría gratuita — Cupo limitado' }}
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-[3.25rem] font-extrabold text-white leading-[1.1] tracking-tight">
                    {!! $campaign['heading'] ?? 'Vende tu departamento en la <span class="text-indigo-400">Colonia del Valle</span> rápido y al mejor precio' !!}
                </h1>

                <p class="mt-6 text-lg text-gray-400 leading-relaxed max-w-xl">
                    {{ $campaign['subheading'] ?? 'Conectamos tu propiedad con compradores calificados. Sin exclusivas forzadas, sin comisiones ocultas.' }}
                </p>

                {{-- Trust signals inline --}}
                <div class="mt-8 flex flex-wrap gap-6 text-sm text-gray-400">
                    <div class="flex items-center gap-2">
                        <x-icon name="check" class="w-5 h-5 text-green-400" />
                        Valuación gratuita
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="check" class="w-5 h-5 text-green-400" />
                        +{{ $stats['years'] ?? '10' }} años de experiencia
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="check" class="w-5 h-5 text-green-400" />
                        {{ $stats['sold'] ?? '200' }}+ propiedades vendidas
                    </div>
                </div>
            </div>

            {{-- Lead Form --}}
            <div class="bg-white rounded-2xl shadow-2xl p-8 lg:p-10" id="formulario">
                <h2 class="text-xl font-bold text-gray-900 mb-1">Solicita tu asesoría gratuita</h2>
                <p class="text-sm text-gray-500 mb-6">Te contactamos en menos de 24 horas.</p>

                @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 mb-4" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-green-600 hover:text-green-800 ml-2">&times;</button>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('landing.submit') }}" class="space-y-4" id="landing-form">
                    @csrf
                    {{-- Honeypot --}}
                    <div class="hidden" aria-hidden="true">
                        <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                    </div>
                    {{-- reCAPTCHA v3 token --}}
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token_landing">

                    {{-- UTM tracking --}}
                    <input type="hidden" name="utm_source" value="{{ request('utm_source') }}">
                    <input type="hidden" name="utm_medium" value="{{ request('utm_medium') }}">
                    <input type="hidden" name="utm_campaign" value="{{ request('utm_campaign', $campaign['slug'] ?? '') }}">

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-3"
                               placeholder="Tu nombre">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-3"
                               placeholder="55 1234 5678">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-3"
                               placeholder="tu@email.com">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">¿Qué tipo de propiedad deseas vender? <span class="text-gray-400 font-normal">(opcional)</span></label>
                        <select name="message" id="message" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-3">
                            <option value="Quiero vender mi departamento">Departamento</option>
                            <option value="Quiero vender mi casa">Casa</option>
                            <option value="Quiero vender un terreno">Terreno</option>
                            <option value="Quiero vender un local comercial">Local comercial</option>
                            <option value="Otro tipo de propiedad">Otro</option>
                        </select>
                    </div>

                    @php
                        try { $privacyDoc = \App\Models\LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first(); } catch (\Exception $e) { $privacyDoc = null; }
                    @endphp
                    @if($privacyDoc)
                    <div class="flex items-start gap-2">
                        <input type="checkbox" name="accept_privacy" id="accept_privacy_landing" required class="mt-1 rounded border-gray-600 bg-gray-700">
                        <label for="accept_privacy_landing" class="text-xs text-gray-400 leading-snug">
                            Acepto el <a href="{{ route('legal.public', $privacyDoc->slug) }}" target="_blank" class="text-indigo-400 underline hover:text-indigo-300">Aviso de Privacidad</a>
                        </label>
                    </div>
                    @endif

                    <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                        Quiero mi asesoría gratuita
                    </button>

                    <p class="text-xs text-gray-400 text-center">Sin compromiso. Tus datos están seguros.</p>
                </form>
            </div>
        </div>
    </div>
</section>


{{-- ======================================================
     2. BENEFICIOS — Por qué elegirnos
     ====================================================== --}}
<section class="py-20 lg:py-24 bg-white" id="beneficios">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">¿Por qué vender con nosotros?</h2>
            <p class="mt-4 text-lg text-gray-500">Más de {{ $stats['years'] ?? '10' }} años ayudando a propietarios en la Colonia del Valle a cerrar la mejor operación.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
                $benefits = $campaign['benefits'] ?? [
                    ['icon' => 'rocket', 'title' => 'Vende más rápido', 'desc' => 'Nuestro promedio de venta es de 45 días gracias a nuestra cartera de compradores activos y estrategia de marketing digital.'],
                    ['icon' => 'users', 'title' => 'Compradores calificados', 'desc' => 'Filtramos a cada prospecto para que solo recibas visitas de personas con capacidad real de compra.'],
                    ['icon' => 'shield', 'title' => 'Asesoría profesional', 'desc' => 'Te acompañamos en cada paso: valuación, negociación, aspectos legales y cierre ante notario.'],
                    ['icon' => 'chart', 'title' => 'Precio justo de mercado', 'desc' => 'Valuación basada en datos reales de la zona para que no subvalores ni sobrevalores tu propiedad.'],
                    ['icon' => 'camera', 'title' => 'Marketing profesional', 'desc' => 'Fotografía profesional, tours virtuales y distribución en los principales portales inmobiliarios.'],
                    ['icon' => 'lock', 'title' => 'Sin exclusivas forzadas', 'desc' => 'Trabajamos por resultados. No te amarramos con contratos de exclusividad que no te convienen.'],
                ];
                $icons = [
                    'rocket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>',
                    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
                    'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>',
                    'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
                    'camera' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>',
                    'lock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>',
                ];
            @endphp

            @foreach($benefits as $b)
            <div class="group rounded-2xl border border-gray-100 p-8 hover:border-indigo-100 hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 mb-5 group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icons[$b['icon']] ?? $icons['shield'] !!}</svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">{{ $b['title'] }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $b['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================
     3. PRUEBA SOCIAL — Métricas + Testimonios
     ====================================================== --}}
<section class="py-20 lg:py-24 bg-gray-50" id="testimonios">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            @php
                $metrics = $campaign['metrics'] ?? [
                    ['value' => '200+', 'label' => 'Propiedades vendidas'],
                    ['value' => '95%', 'label' => 'Clientes satisfechos'],
                    ['value' => '45', 'label' => 'Días promedio de venta'],
                    ['value' => '10+', 'label' => 'Años de experiencia'],
                ];
            @endphp
            @foreach($metrics as $m)
            <div class="text-center p-6 rounded-2xl bg-white border border-gray-100">
                <div class="text-3xl sm:text-4xl font-bold text-indigo-600">{{ $m['value'] }}</div>
                <div class="mt-1 text-sm text-gray-500">{{ $m['label'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Testimonials --}}
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">Lo que dicen nuestros clientes</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @php
                $testimonials = $campaign['testimonials'] ?? [
                    ['name' => 'María González', 'role' => 'Vendió depto. en Del Valle Centro', 'text' => 'Vendimos nuestro departamento en solo 38 días. El equipo fue increíblemente profesional y nos mantuvieron informados en cada paso del proceso.', 'stars' => 5],
                    ['name' => 'Roberto Martínez', 'role' => 'Vendió casa en Del Valle Sur', 'text' => 'Lo que más me gustó fue la transparencia. Desde el inicio me dieron un precio realista y lo lograron superar en la negociación final.', 'stars' => 5],
                    ['name' => 'Ana Lucía Pérez', 'role' => 'Vendió depto. en Narvarte', 'text' => 'Después de 6 meses con otra inmobiliaria, cambié a Home del Valle y en 2 meses ya tenía comprador. La diferencia fue enorme.', 'stars' => 5],
                ];
            @endphp

            @foreach($testimonials as $t)
            <div class="rounded-2xl bg-white border border-gray-100 p-8">
                <div class="flex gap-0.5 mb-4">
                    @for($i = 0; $i < ($t['stars'] ?? 5); $i++)
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <blockquote class="text-gray-600 leading-relaxed mb-6">"{{ $t['text'] }}"</blockquote>
                <div>
                    <div class="font-semibold text-sm text-gray-900">{{ $t['name'] }}</div>
                    <div class="text-xs text-gray-400">{{ $t['role'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================
     4. PROCESO — 3 pasos simples
     ====================================================== --}}
<section class="py-20 lg:py-24 bg-white" id="proceso">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">Así de fácil es vender tu propiedad</h2>
            <p class="mt-4 text-lg text-gray-500">Un proceso simple, transparente y sin sorpresas.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
            @php
                $steps = $campaign['steps'] ?? [
                    ['num' => '01', 'title' => 'Solicita tu asesoría', 'desc' => 'Llena el formulario y un asesor te contactará en menos de 24 horas para agendar una visita a tu propiedad.'],
                    ['num' => '02', 'title' => 'Valuación y estrategia', 'desc' => 'Realizamos una valuación profesional gratuita y diseñamos un plan de venta personalizado con marketing digital.'],
                    ['num' => '03', 'title' => 'Vende al mejor precio', 'desc' => 'Gestionamos las visitas, negociación y cierre legal. Tú solo firmas ante notario y recibes tu pago.'],
                ];
            @endphp

            @foreach($steps as $s)
            <div class="relative text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600 text-white text-2xl font-bold mb-6">
                    {{ $s['num'] }}
                </div>
                <h3 class="text-xl font-bold mb-3">{{ $s['title'] }}</h3>
                <p class="text-gray-500 leading-relaxed">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================
     5. CTA FUERTE — Repetir formulario
     ====================================================== --}}
<section class="py-20 lg:py-24 bg-indigo-600">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
            {{ $campaign['cta_heading'] ?? '¿Listo para vender tu propiedad?' }}
        </h2>
        <p class="mt-4 text-lg text-indigo-200 max-w-2xl mx-auto">
            {{ $campaign['cta_subheading'] ?? 'Solicita tu asesoría gratuita hoy y descubre cuánto vale realmente tu propiedad en el mercado actual.' }}
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="#formulario"
               class="inline-flex items-center rounded-lg bg-white px-8 py-4 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 transition-colors">
                Solicitar asesoría gratuita
            </a>
            @if($siteSettings?->whatsapp_number)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}?text={{ urlencode($campaign['wa_message'] ?? 'Hola, me interesa vender mi propiedad en Colonia del Valle') }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-lg border-2 border-white/30 px-8 py-4 text-sm font-semibold text-white hover:bg-white/10 transition-colors">
                <x-icon name="brands/whatsapp" class="w-5 h-5" />
                WhatsApp directo
            </a>
            @endif
        </div>
    </div>
</section>


{{-- ======================================================
     6. FAQ — SEO optimized
     ====================================================== --}}
<section class="py-20 lg:py-24 bg-gray-50" id="preguntas">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold tracking-tight">Preguntas frecuentes</h2>
            <p class="mt-4 text-gray-500">Resolvemos tus dudas más comunes sobre vender tu propiedad.</p>
        </div>

        <div class="space-y-4">
            @foreach($faqs as $faq)
            <div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                <button @click="open = !open" class="flex items-center justify-between w-full px-6 py-5 text-left">
                    <span class="font-semibold text-gray-900 pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-gray-500 leading-relaxed text-sm">{{ $faq['a'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ======================================================
     FOOTER MINIMAL
     ====================================================== --}}
<footer class="bg-gray-950 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-center gap-4 text-sm text-gray-500">
        <span>&copy; {{ date('Y') }} {{ $siteSettings?->site_name ?? 'Home del Valle' }} Bienes Raíces. Todos los derechos reservados.</span>
        <div class="flex items-center gap-4">
            <a href="{{ url('/legal/aviso-de-privacidad') }}" class="hover:text-gray-400 transition-colors">Aviso de privacidad</a>
            <a href="{{ url('/legal/terminos-y-condiciones') }}" class="hover:text-gray-400 transition-colors">Términos y condiciones</a>
            <a href="{{ url('/legal/politica-de-cookies') }}" class="hover:text-gray-400 transition-colors">Política de cookies</a>
            <a href="{{ route('login') }}" class="hover:text-gray-400 transition-colors">Acceso (Office)</a>
        </div>
    </div>
</footer>

@endsection

@section('scripts')
@if(config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('landing-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var tokenInput = document.getElementById('recaptcha_token_landing');
            if (tokenInput && !tokenInput.value) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'landing'}).then(function(token) {
                        tokenInput.value = token;
                        form.submit();
                    });
                });
            }
        });
    }
});
</script>
@endif
@endsection
