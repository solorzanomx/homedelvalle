@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        :title="$post->meta_title ?: $post->title"
        :description="$post->meta_description ?: $post->excerpt"
        :canonical="url('/blog/' . $post->slug)"
        :og-image="$post->featured_image ? url(Storage::url($post->featured_image)) : null"
        og-type="article"
    />

    {{-- Preload featured image for LCP --}}
    @if($post->featured_image)
    <link rel="preload" as="image" href="{{ url(Storage::url($post->featured_image)) }}">
    @endif

    {{-- Article schema --}}
    <x-public.json-ld type="Article" :data="array_filter([
        'headline'      => $post->meta_title ?: $post->title,
        'datePublished' => $post->published_at?->toIso8601String(),
        'dateModified'  => $post->updated_at?->toIso8601String(),
        'author'        => $post->author
            ? ['@type' => 'Person', 'name' => trim($post->author->name . ' ' . ($post->author->last_name ?? '')), 'jobTitle' => $post->author->title ?: 'Broker en Benito Juárez', 'url' => url('/nosotros'), 'worksFor' => ['@type' => 'Organization', 'name' => 'Home del Valle Bienes Raíces', 'url' => url('/')]]
            : ['@type' => 'Organization', 'name' => 'Home del Valle Bienes Raíces', 'url' => url('/')],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => 'Home del Valle Bienes Raíces',
            'url'   => url('/'),
            'logo'  => ['@type' => 'ImageObject', 'url' => asset('images/logo.png')],
        ],
        'url'           => url('/blog/' . $post->slug),
        'description'   => $post->meta_description ?: $post->excerpt,
        'image'         => $post->featured_image ? url(Storage::url($post->featured_image)) : null,
    ])" />

    {{-- FAQPage schema — solo cuando el post tiene preguntas frecuentes configuradas --}}
    @if($post->faq_schema && count($post->faq_schema))
    <x-public.json-ld type="FAQPage" :data="[
        'mainEntity' => collect($post->faq_schema)->map(fn($item) => [
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ])->all(),
    ]" />
    @endif

    {{-- BreadcrumbList schema --}}
    <x-public.json-ld type="BreadcrumbList" :data="[
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog',   'item' => url('/blog')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post->meta_title ?: $post->title, 'item' => url('/blog/' . $post->slug)],
        ],
    ]" />
@endsection

@section('content')
    <style>
        .prose ul, .prose ol { margin-top: 0.5rem; margin-bottom: 0.5rem; }
        .prose li { margin-top: 0.15rem; margin-bottom: 0.15rem; line-height: 1.5; }
        .prose li p { margin: 0; }
    </style>
    {{-- Hero --}}
    @if($post->featured_image)
    <section class="relative overflow-hidden bg-brand-950">
        <picture>
            @if($post->featured_image_webp_lg)
            <source type="image/webp" srcset="{{ $post->featured_image_webp_lg }}">
            @endif
            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover opacity-30" fetchpriority="high" loading="eager">
        </picture>
        <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-950/70 to-brand-950/40"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.12)_0%,_transparent_60%)]"></div>

        <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-24 pb-16 sm:pt-32 sm:pb-20 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <div class="flex items-center justify-center gap-3 mb-5">
                @if($post->category)
                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-500/20 text-brand-200 border border-brand-400/20">{{ $post->category->name }}</span>
                @endif
                <span class="text-xs text-brand-300/60">{{ $post->published_at->translatedFormat('d M, Y') }}</span>
                @if($post->author)
                <span class="text-xs text-brand-300/60">por {{ $post->author->name }}</span>
                @endif
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">{{ $post->title }}</h1>

            {{-- Breadcrumbs --}}
            <nav class="mt-6 flex items-center justify-center gap-2 text-sm text-brand-300/60" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="hover:text-white transition-colors duration-200">Inicio</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <a href="{{ url('/blog') }}" class="hover:text-white transition-colors duration-200">Blog</a>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="text-brand-200/80 truncate max-w-[200px]">{{ $post->title }}</span>
            </nav>
        </div>
    </section>
    @else
    <x-public.hero
        :heading="$post->title"
        :breadcrumb-items="[['label' => 'Blog', 'url' => url('/blog')], ['label' => $post->title]]"
    />
    @endif

    {{-- Article --}}
    <section class="py-16 sm:py-20 bg-white">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            {{-- Meta bar --}}
            <div class="flex flex-wrap items-center gap-4 mb-8 pb-8 border-b border-gray-100" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                @if($post->category)
                <a href="{{ url('/blog?category=' . $post->category->slug) }}" class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-600 hover:bg-brand-100 transition-colors duration-200">{{ $post->category->name }}</a>
                @endif
                <span class="text-sm text-gray-400">{{ $post->published_at->translatedFormat('d \d\e F, Y') }}</span>
                @if($post->author)
                <span class="text-sm text-gray-400">por <span class="font-medium text-gray-600">{{ $post->author->name }}</span></span>
                @endif
                <span class="text-sm text-gray-400">{{ number_format($post->views_count ?? 0) }} vistas</span>
            </div>

            {{-- Tags --}}
            @if($post->tags->count())
            <div class="flex flex-wrap gap-2 mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                @foreach($post->tags as $tag)
                <a href="{{ url('/blog?tag=' . $tag->slug) }}"
                   class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-500 border border-gray-200/60 hover:border-brand-200 hover:text-brand-600 hover:bg-brand-50/50 transition-colors duration-200">{{ $tag->name }}</a>
                @endforeach
            </div>
            @endif

            {{-- Article body — post-procesado por BlogBodyEnhancer: links de
                 colonias en tablas → Observatorio, CTA de opinión de valor
                 tras la primera tabla, banner predio→desarrolladora en el
                 cierre, y el cuerpo partido a media lectura para embeber el
                 mini-form de captura (Livewire no puede vivir dentro del
                 HTML crudo que viene de BD). --}}
            @php
                $proseClasses = 'prose prose-lg prose-gray max-w-none
                prose-headings:font-extrabold prose-headings:tracking-tight prose-headings:text-gray-900
                prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4 prose-h2:pb-3 prose-h2:border-b prose-h2:border-gray-100
                prose-h3:text-xl prose-h3:mt-8 prose-h3:mb-3
                prose-p:text-gray-600 prose-p:leading-[1.85]
                prose-a:text-brand-600 prose-a:font-semibold prose-a:no-underline prose-a:border-b prose-a:border-brand-200 hover:prose-a:border-brand-500 hover:prose-a:text-brand-700
                prose-strong:text-gray-900 prose-strong:font-bold
                prose-blockquote:border-l-4 prose-blockquote:border-brand-500 prose-blockquote:bg-brand-50/40 prose-blockquote:rounded-r-xl prose-blockquote:py-4 prose-blockquote:px-6 prose-blockquote:not-italic prose-blockquote:text-gray-700
                prose-img:rounded-2xl prose-img:shadow-premium-lg prose-img:my-8
                prose-pre:bg-brand-950 prose-pre:rounded-xl prose-pre:shadow-premium
                prose-code:text-brand-700 prose-code:bg-brand-50 prose-code:rounded-md prose-code:px-1.5 prose-code:py-0.5 prose-code:text-sm prose-code:font-medium prose-code:before:content-none prose-code:after:content-none
                prose-ul:my-4 prose-ol:my-4 prose-ul:space-y-1 prose-ol:space-y-1
                prose-li:text-gray-600 prose-li:leading-normal prose-li:marker:text-brand-400 prose-li:my-0
                prose-hr:border-gray-200 prose-hr:my-10
                prose-table:overflow-hidden prose-table:rounded-xl prose-table:border prose-table:border-gray-200
                prose-th:bg-brand-50 prose-th:text-brand-700 prose-th:font-bold prose-th:text-sm prose-th:uppercase prose-th:tracking-wider
                prose-td:text-gray-600 prose-td:text-sm';

                // El CTA "promedios de zona" tras la primera tabla asume que
                // esa tabla es de precios — falso en categorías fiscales/
                // legales, donde la primera tabla suele ser de tasas de ISR
                // o requisitos (bug real reportado: CTA de precios inyectado
                // justo después de una tabla de ISR, fuera de contexto).
                $categoriasSinCtaValuacion = ['herencias-y-sucesiones', 'expertos-insights'];
                $valuationCtaHtml = in_array($post->category?->slug, $categoriasSinCtaValuacion, true)
                    ? ''
                    : view('blog._cta-valuacion')->render();

                $isHerencia = $post->category?->slug === 'herencias-y-sucesiones';

                // Solo en herencias: el bloque predio→desarrolladora se mueve
                // justo después de "Ejemplo práctico" (el heredero de casa
                // vieja es el prospecto exacto de ese funnel) en vez de ir al
                // final del post — decisión confirmada, resto de categorías
                // sin cambio.
                $enhanced = \App\Support\BlogBodyEnhancer::enhance(
                    $post->rendered_body,
                    $valuationCtaHtml,
                    view('blog._cta-predio', ['post' => $post])->render(),
                    $post->title,
                    $isHerencia ? 'Ejemplo pr' : null,
                );
            @endphp
            <article class="{{ $proseClasses }}"
                x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                {!! $enhanced['first'] !!}
            </article>

            <livewire:forms.blog-quick-valuation-form :source-page="'/blog/' . $post->slug" :is-herencia="$isHerencia" />

            @if($enhanced['second'] !== '')
            <article class="{{ $proseClasses }}">
                {!! $enhanced['second'] !!}
            </article>
            @endif

            {{-- Auto CTA by category — se SUPRIME si el cuerpo del post ya
                 termina con un CTA propio (el {{CTA2}} de la BD suele vivir
                 al final): dos tarjetas de CTA encimadas al cierre se
                 canibalizan entre sí (bug real reportado con captura). --}}
            @php
                $bodyTail = mb_substr($enhanced['second'] !== '' ? $enhanced['second'] : $enhanced['first'], -2200);
                $bodyEndsWithCta = str_contains($bodyTail, 'not-prose my-10');
                $slug = $post->category?->slug ?? '';
                $ctaMap = [
                    'vender-tu-propiedad'        => ['icon' => 'home',         'title' => '¿Quieres vender tu propiedad?',             'desc' => 'Opinión de valor gratuita en 24 horas, venta en 45–60 días y seguridad jurídica completa.',        'btn' => 'Solicitar opinión de valor gratuita', 'url' => route('landing.vende')],
                    'comprar'                    => ['icon' => 'search',       'title' => '¿Buscas propiedad en la Benito Juárez?',    'desc' => 'Propiedades seleccionadas con asesoría personalizada y acompañamiento legal de inicio a fin.',    'btn' => 'Ver propiedades disponibles', 'url' => route('propiedades.index')],
                    'inversion-inmobiliaria'     => ['icon' => 'trending-up',  'title' => '¿Te interesa invertir en bienes raíces?',   'desc' => 'Conoce las mejores oportunidades de inversión en Colonia del Valle y Benito Juárez.',          'btn' => 'Hablar con un asesor',        'url' => route('contacto')],
                    // OJO: 'bar-chart-2' NO existe en el set de íconos — tronaba con 500
                    // en cuanto un post caía en esta categoría (bug latente encontrado
                    // al re-categorizar el post de precios). Verificar íconos nuevos
                    // con Blade::render antes de usarlos.
                    'mercado-inmobiliario-cdmx'  => ['icon' => 'bar-chart-3',  'title' => '¿Cuánto vale tu propiedad hoy?',            'desc' => 'Obtén un precio de mercado actualizado con nuestra opinión de valor sin costo, generada por el Observatorio de precios.',           'btn' => 'Valúa tu propiedad',          'url' => route('landing.vende')],
                    'colonias-de-benito-juarez'  => ['icon' => 'map-pin',      'title' => '¿Buscas propiedad en Benito Juárez?',       'desc' => 'Conocemos cada colonia a fondo. Encuentra tu propiedad ideal con asesoría especializada.',       'btn' => 'Ver propiedades disponibles', 'url' => route('propiedades.index')],
                    'expertos-insights'          => ['icon' => 'shield-check', 'title' => '¿Tienes dudas sobre tu operación?',         'desc' => 'Nuestro equipo te orienta en cada etapa: opinión de valor, contrato, escrituración y entrega.',         'btn' => 'Consulta gratuita',           'url' => route('contacto')],
                    'zonificacion-desarrollo'    => ['icon' => 'trending-up',  'title' => '¿Tu casa o predio tiene potencial de desarrollo?', 'desc' => 'Constructoras de nuestra cartera buscan predios en Benito Juárez ahora mismo. Evaluación gratuita, confidencial y sin compromiso.', 'btn' => 'Evaluar mi propiedad como terreno', 'url' => route('landing.vende-desarrolladora')],
                    'herencias-y-sucesiones'     => ['icon' => 'shield-check', 'title' => '¿Heredaste una propiedad y no sabes por dónde empezar?', 'desc' => 'Te acompañamos de la sucesión a la venta: orientación legal, opinión de valor gratuita y venta segura.', 'btn' => 'Recibir orientación gratuita', 'url' => route('landing.vende')],
                ];
                $cta = $ctaMap[$slug] ?? ['icon' => 'message-circle', 'title' => '¿Tienes una propiedad en la Benito Juárez?', 'desc' => 'Platícanos tu caso. Asesoría personalizada, sin costo y sin compromiso.', 'btn' => 'Contactar a un asesor', 'url' => route('contacto')];
            @endphp
            @if(!$bodyEndsWithCta)
            <div class="mt-10 not-prose" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-brand-50 to-white border border-brand-100">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-brand-500 rounded-l-2xl"></div>
                    <div class="p-8 sm:p-10 pl-10 sm:pl-12 flex flex-col sm:flex-row sm:items-center gap-6">
                        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-500 shrink-0">
                            <x-icon name="{{ $cta['icon'] }}" class="w-6 h-6 text-white" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-extrabold text-gray-900">{{ $cta['title'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ $cta['desc'] }}</p>
                        </div>
                        {{-- style de fondo obligatorio: sin él el botón queda
                             texto blanco sobre fondo claro — invisible (bug
                             real reportado, el CTA final "no tenía botón"). --}}
                        <a href="{{ $cta['url'] }}"
                           data-track-location="cta_auto_final"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold transition-all duration-300 hover:-translate-y-0.5 shrink-0 shadow-brand"
                           style="background: var(--color-primary, #3B82C4);">
                            {{ $cta['btn'] }}
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Caja de autor — señal E-E-A-T para contenido de dinero/legal:
                 Google premia que un artículo de precios o herencias lo firme
                 una persona real con credenciales, no una marca anónima. --}}
            @if($post->author)
            <div class="mt-8 not-prose rounded-2xl border border-gray-200/60 bg-gray-50/60 p-6 sm:p-7 flex items-start gap-5" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="w-16 h-16 rounded-full overflow-hidden shrink-0 ring-4 ring-brand-100">
                    @if($post->author->avatar_path)
                        <img src="{{ Storage::url($post->author->avatar_path) }}" alt="{{ trim($post->author->name . ' ' . ($post->author->last_name ?? '')) }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-xl font-bold">
                            {{ strtoupper(substr($post->author->name, 0, 1)) }}{{ strtoupper(substr($post->author->last_name ?? '', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[0.65rem] font-bold tracking-widest uppercase text-gray-400">Escrito por</p>
                    <p class="mt-1 text-base font-extrabold text-gray-900">{{ trim($post->author->name . ' ' . ($post->author->last_name ?? '')) }}</p>
                    <p class="text-sm text-brand-600 font-medium">{{ $post->author->title ?: 'Broker en Benito Juárez · 30+ años de experiencia' }}</p>
                    @if($post->author->bio)
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $post->author->bio }}</p>
                    @endif
                    <a href="{{ route('nosotros') }}" class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                        Conoce al equipo
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </div>
            @endif

            {{-- Enlace al Observatorio si el post tiene zona relacionada --}}
            @if($post->zona_mercado_slug)
            @php $zonaNombre = ucwords(str_replace(['-', '_'], ' ', $post->zona_mercado_slug)); @endphp
            <aside style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:1.25rem 1.5rem;margin:2rem 0;">
                <p style="font-size:.75rem;color:#0369a1;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Datos de mercado relacionados</p>
                <p style="font-size:.95rem;font-weight:600;color:#0c4a6e;margin-bottom:.75rem;">Consulta los precios actuales en {{ $zonaNombre }}</p>
                <a href="{{ route('precios.zone', $post->zona_mercado_slug) }}"
                   style="display:inline-flex;align-items:center;gap:.4rem;background:#0ea5e9;color:#fff;padding:.5rem 1.1rem;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;">
                    Ver precio por m² →
                </a>
            </aside>
            @endif

            {{-- Share buttons --}}
            <div class="mt-12 pt-8 border-t border-gray-100" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-gray-900 mb-4">Compartir este artículo</p>
                <div class="flex gap-3">
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' - ' . url('/blog/' . $post->slug)) }}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-[#25D366]/10 hover:text-[#25D366] transition-all duration-300" aria-label="Compartir en WhatsApp">
                        <x-icon name="brands/whatsapp" class="w-4.5 h-4.5" />
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/blog/' . $post->slug)) }}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300" aria-label="Compartir en Facebook">
                        <x-icon name="brands/facebook" class="w-4.5 h-4.5" />
                    </a>
                    <button onclick="navigator.clipboard.writeText('{{ url('/blog/' . $post->slug) }}'); this.querySelector('span').textContent = '¡Copiado!'; setTimeout(() => this.querySelector('span').textContent = 'Copiar enlace', 2000)"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all duration-300 text-sm">
                        <x-icon name="share-2" class="w-4 h-4" />
                        <span>Copiar enlace</span>
                    </button>
                </div>
            </div>

            {{-- Back to blog --}}
            <div class="mt-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ url('/blog') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors duration-200">
                    <x-icon name="arrow-left" class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" />
                    Volver al blog
                </a>
            </div>
        </div>
    </section>

    {{-- Related posts --}}
    @if(isset($related) && $related->count())
    <section class="py-20 sm:py-24 bg-gray-50/60">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Sigue leyendo</p>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Artículos relacionados</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($related as $index => $relPost)
                <article class="group" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 150 }}ms">
                    @include('blog._card', ['post' => $relPost])
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
@endsection
