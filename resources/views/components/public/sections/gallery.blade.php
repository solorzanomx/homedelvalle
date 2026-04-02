@php $d = $data; $images = $d['images'] ?? []; @endphp
@if(count($images))
<section class="py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($images as $img)
            <div class="rounded-xl overflow-hidden aspect-square group">
                <img src="{{ $img['url'] ?? '' }}" alt="{{ $img['caption'] ?? '' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
