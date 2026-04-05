@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Blog"
        description="Noticias, consejos y novedades del sector inmobiliario en Ciudad de México."
        :canonical="url('/blog')"
    />
@endsection

@php
    $template = $siteSettings?->blog_template ?? 'grid';
@endphp

@section('content')
    <x-public.hero
        heading="{{ $siteSettings?->blog_heading ?? 'Blog' }}"
        subheading="{{ $siteSettings?->blog_subheading ?? 'Noticias, consejos y novedades del sector inmobiliario' }}"
        :breadcrumb-items="[['label' => 'Blog']]"
    />

    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Category filter --}}
            @if($categories->count())
            <div class="flex flex-wrap items-center justify-center gap-2.5 mb-12" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <a href="{{ url('/blog') }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300
                          {{ !request('category') ? 'gradient-brand text-white shadow-brand' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:border-brand-200 hover:text-brand-600 hover:bg-brand-50/50' }}">
                    Todos
                </a>
                @foreach($categories as $cat)
                <a href="{{ url('/blog?category=' . $cat->slug) }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-300
                          {{ request('category') === $cat->slug ? 'gradient-brand text-white shadow-brand' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:border-brand-200 hover:text-brand-600 hover:bg-brand-50/50' }}">
                    {{ $cat->name }}
                    <span class="text-xs opacity-70">({{ $cat->posts_count }})</span>
                </a>
                @endforeach
            </div>
            @endif

            {{-- Posts --}}
            @if($posts->count())

                @if($template === 'list')
                    {{-- LIST TEMPLATE --}}
                    <div class="space-y-6">
                        @foreach($posts as $index => $post)
                        <article class="group" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ ($index % 5) * 100 }}ms">
                            <a href="{{ url('/blog/' . $post->slug) }}" class="flex flex-col sm:flex-row gap-6 rounded-2xl border border-gray-200/60 bg-white p-4 hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-0.5 transition-all duration-500">
                                <div class="sm:w-72 shrink-0 aspect-[16/10] rounded-xl overflow-hidden bg-gray-100">
                                    @if($post->featured_image)
                                        <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center">
                                            <svg class="w-10 h-10 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <div class="flex items-center gap-3 mb-3">
                                        @if($post->category)
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-600">{{ $post->category->name }}</span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $post->published_at->translatedFormat('d M, Y') }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand-600 transition-colors duration-300 line-clamp-2">{{ $post->title }}</h3>
                                    @if($post->excerpt)
                                    <p class="mt-2 text-sm text-gray-500 line-clamp-2 leading-relaxed">{{ $post->excerpt }}</p>
                                    @endif
                                    <span class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600">
                                        Leer más
                                        <x-icon name="arrow-right" class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-1" />
                                    </span>
                                </div>
                            </a>
                        </article>
                        @endforeach
                    </div>

                @elseif($template === 'magazine')
                    {{-- MAGAZINE TEMPLATE --}}
                    @if($posts->first())
                    <article class="group mb-10" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                        <a href="{{ url('/blog/' . $posts->first()->slug) }}" class="flex flex-col lg:flex-row gap-8 rounded-2xl border border-gray-200/60 bg-white overflow-hidden hover:shadow-premium-lg hover:border-brand-100 transition-all duration-500">
                            <div class="lg:w-3/5 aspect-[16/9] lg:aspect-auto overflow-hidden bg-gray-100">
                                @if($posts->first()->featured_image)
                                    <img src="{{ Storage::url($posts->first()->featured_image) }}" alt="{{ $posts->first()->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
                                @else
                                    <div class="w-full h-full min-h-[300px] bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="lg:w-2/5 flex flex-col justify-center p-6 lg:p-10 lg:pl-0">
                                <div class="flex items-center gap-3 mb-4">
                                    @if($posts->first()->category)
                                    <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-600">{{ $posts->first()->category->name }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400">{{ $posts->first()->published_at->translatedFormat('d M, Y') }}</span>
                                </div>
                                <h3 class="text-2xl font-extrabold text-gray-900 group-hover:text-brand-600 transition-colors duration-300 tracking-tight">{{ $posts->first()->title }}</h3>
                                @if($posts->first()->excerpt)
                                <p class="mt-3 text-gray-500 leading-relaxed line-clamp-3">{{ $posts->first()->excerpt }}</p>
                                @endif
                                <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-brand-600">
                                    Leer artículo
                                    <x-icon name="arrow-right" class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                                </span>
                            </div>
                        </a>
                    </article>
                    @endif

                    @if($posts->count() > 1)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($posts->slice(1) as $index => $post)
                        <article class="group" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $index * 100 }}ms">
                            @include('blog._card', ['post' => $post])
                        </article>
                        @endforeach
                    </div>
                    @endif

                @else
                    {{-- GRID TEMPLATE (default) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($posts as $index => $post)
                        <article class="group" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ ($index % 6) * 100 }}ms">
                            @include('blog._card', ['post' => $post])
                        </article>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination --}}
                @if($posts->hasPages())
                <div class="mt-14 flex justify-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                    {{ $posts->links() }}
                </div>
                @endif

            @else
                {{-- Empty state --}}
                <div class="text-center py-20 rounded-2xl gradient-brand-soft" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto rounded-2xl bg-brand-100 mb-5">
                        <svg class="w-8 h-8 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No hay artículos todavía</h3>
                    <p class="mt-2 text-gray-500">Pronto publicaremos contenido interesante. Vuelve pronto.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
