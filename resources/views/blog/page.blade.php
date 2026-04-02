@extends($layout ?? 'layouts.public')

@section('meta')
    <x-public.seo-meta
        :title="$page->title"
        :canonical="url('/p/' . $page->slug)"
    />
    @if($page->is_landing && !empty($page->landing_settings['custom_css']))
    <style>{!! $page->landing_settings['custom_css'] !!}</style>
    @endif
@endsection

@section('content')
    @if($page->use_sections && !empty($page->sections))
        {{-- Section-based page --}}
        @include('components.public.page-sections', ['sections' => $page->sections])
    @else
        {{-- Traditional body-based page --}}
        <x-public.hero
            :heading="$page->title"
            :breadcrumb-items="[['label' => $page->title]]"
        />

        <section class="py-16 sm:py-20 bg-white">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <article class="prose prose-lg prose-gray max-w-none
                    prose-headings:font-extrabold prose-headings:tracking-tight
                    prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline
                    prose-blockquote:border-l-brand-500 prose-blockquote:bg-brand-50/30 prose-blockquote:rounded-r-xl
                    prose-img:rounded-2xl prose-img:shadow-premium
                    prose-pre:bg-brand-950 prose-pre:rounded-xl">
                    {!! $page->body !!}
                </article>
            </div>
        </section>

        {{-- CTA Section --}}
        <section class="py-24 sm:py-32 gradient-brand-soft" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $siteSettings?->cta_heading ?? '¿Listo para encontrar tu hogar ideal?' }}</h2>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">{{ $siteSettings?->cta_subheading ?? 'Explora nuestro catálogo de propiedades o contáctanos para asesoría personalizada.' }}</p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('propiedades.index') }}" class="rounded-xl gradient-brand px-7 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        Explorar propiedades
                    </a>
                    <a href="{{ route('contacto') }}" class="rounded-xl border border-gray-200 bg-white px-7 py-4 text-sm font-semibold text-gray-700 hover:border-brand-200 hover:text-brand-600 transition-all duration-300">
                        Contáctanos
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection
