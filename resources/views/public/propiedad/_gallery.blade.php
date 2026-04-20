@php
    $photos = $property->photos->sortBy(fn($p) => $p->is_primary ? 0 : 1)->values();
    $photoCount = $photos->count();
@endphp

@if($photoCount > 1)
<div x-data="propertyGallery()" class="mt-8 mb-2" x-intersect.once="$el.classList.add('animate-fade-in-up')">
    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest mb-4">Galeria de fotos</p>

    {{-- Thumbnail grid --}}
    <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-2">
        @foreach($photos as $i => $photo)
        <button @click="open({{ $i }})" class="aspect-square rounded-xl overflow-hidden bg-gray-100 hover:opacity-80 transition-opacity focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 {{ $i === 0 ? 'ring-2 ring-brand-500' : '' }}">
            <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->description ?? $property->title }}" class="w-full h-full object-cover" loading="lazy">
        </button>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <div x-show="showing" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm" @click.self="close()" @keydown.escape.window="close()" @keydown.left.window="prev()" @keydown.right.window="next()">

        {{-- Close --}}
        <button @click="close()" class="absolute top-4 right-4 text-white/70 hover:text-white transition-colors z-10">
            <x-icon name="x" class="w-7 h-7" />
        </button>

        {{-- Counter --}}
        <div class="absolute top-4 left-4 text-white/60 text-sm font-medium z-10">
            <span x-text="current + 1"></span> / {{ $photoCount }}
        </div>

        {{-- Prev --}}
        <button @click="prev()" class="absolute left-3 sm:left-6 text-white/60 hover:text-white transition-colors z-10 p-2">
            <x-icon name="chevron-left" class="w-8 h-8" />
        </button>

        {{-- Image --}}
        <img :src="photos[current]?.src" :alt="photos[current]?.desc" class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg shadow-2xl select-none">

        {{-- Next --}}
        <button @click="next()" class="absolute right-3 sm:right-6 text-white/60 hover:text-white transition-colors z-10 p-2">
            <x-icon name="chevron-right" class="w-8 h-8" />
        </button>

        {{-- Description --}}
        <div x-show="photos[current]?.desc" class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-black/60 text-white text-sm px-4 py-2 rounded-lg max-w-md text-center" x-text="photos[current]?.desc"></div>
    </div>
</div>

<script>
function propertyGallery() {
    return {
        showing: false,
        current: 0,
        photos: @json($photos->map(fn($p) => ['src' => asset('storage/' . $p->path), 'desc' => $p->description ?? ''])->values()),
        open(i) { this.current = i; this.showing = true; document.body.style.overflow = 'hidden'; },
        close() { this.showing = false; document.body.style.overflow = ''; },
        prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length; },
        next() { this.current = (this.current + 1) % this.photos.length; },
    };
}
</script>
@elseif($photoCount === 1)
{{-- Single photo, no gallery needed --}}
@endif
