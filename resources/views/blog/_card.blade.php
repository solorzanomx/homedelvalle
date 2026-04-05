<a href="{{ url('/blog/' . $post->slug) }}" class="block bg-white rounded-2xl border border-gray-200/60 overflow-hidden hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-1 transition-all duration-500">
    <div class="aspect-[16/10] overflow-hidden bg-gray-100">
        @if($post->featured_image)
            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
        @else
            <div class="w-full h-full bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center">
                <svg class="w-10 h-10 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-center gap-3 mb-3">
            @if($post->category)
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-600">{{ $post->category->name }}</span>
            @endif
            <span class="text-xs text-gray-400">{{ $post->published_at->translatedFormat('d M, Y') }}</span>
        </div>
        <h3 class="text-base font-bold text-gray-900 group-hover:text-brand-600 transition-colors duration-300 line-clamp-2">{{ $post->title }}</h3>
        @if($post->excerpt)
        <p class="mt-2 text-sm text-gray-500 line-clamp-2 leading-relaxed">{{ $post->excerpt }}</p>
        @endif
        <span class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600">
            Leer más
            <x-icon name="arrow-right" class="w-3.5 h-3.5 transition-transform duration-300 group-hover:translate-x-1" />
        </span>
    </div>
</a>
