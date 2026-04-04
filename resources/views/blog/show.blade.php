@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        :title="$post->meta_title ?: $post->title"
        :description="$post->meta_description ?: $post->excerpt"
        :canonical="url('/blog/' . $post->slug)"
        :og-image="$post->featured_image ? Storage::url($post->featured_image) : null"
        og-type="article"
    />
@endsection

@section('content')
    {{-- Hero --}}
    @if($post->featured_image)
    <section class="relative overflow-hidden bg-brand-950">
        <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-t from-brand-950 via-brand-950/70 to-brand-950/40"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.12)_0%,_transparent_60%)]"></div>

        <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-24 pb-16 sm:pt-32 sm:pb-20 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <div class="flex items-center justify-center gap-3 mb-5">
                @if($post->category)
                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-brand-500/20 text-brand-200 border border-brand-400/20">{{ $post->category->name }}</span>
                @endif
                <span class="text-xs text-brand-300/60">{{ $post->published_at->format('d M, Y') }}</span>
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
                <span class="text-sm text-gray-400">{{ $post->published_at->format('d \d\e F, Y') }}</span>
                @if($post->author)
                <span class="text-sm text-gray-400">por <span class="font-medium text-gray-600">{{ $post->author->name }}</span></span>
                @endif
                <span class="text-sm text-gray-400">{{ number_format($post->views_count ?? 0) }} vistas</span>
            </div>

            {{-- Tags --}}
            @if($post->tags->count())
            <div class="flex flex-wrap gap-2 mb-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                @foreach($post->tags as $tag)
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-500 border border-gray-200/60">{{ $tag->name }}</span>
                @endforeach
            </div>
            @endif

            {{-- Article body --}}
            <article class="prose prose-lg prose-gray max-w-none
                prose-headings:font-extrabold prose-headings:tracking-tight
                prose-a:text-brand-600 prose-a:no-underline hover:prose-a:underline
                prose-blockquote:border-l-brand-500 prose-blockquote:bg-brand-50/30 prose-blockquote:rounded-r-xl prose-blockquote:py-1
                prose-img:rounded-2xl prose-img:shadow-premium
                prose-pre:bg-brand-950 prose-pre:rounded-xl"
                x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                {!! nl2br(e($post->body)) !!}
            </article>

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
