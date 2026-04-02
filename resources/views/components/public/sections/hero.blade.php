@php $d = $data; @endphp
@if(!empty($d['heading']) || !empty($d['subheading']))
<section class="relative py-24 sm:py-32 bg-brand-950 overflow-hidden">
    @if(!empty($d['bg_image']))
    <div class="absolute inset-0">
        <img src="{{ $d['bg_image'] }}" alt="" class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-b from-brand-950/80 to-brand-950/95"></div>
    </div>
    @endif
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight">{{ $d['heading'] ?? '' }}</h1>
        @if(!empty($d['subheading']))
        <p class="mt-4 text-xl text-white/70 max-w-3xl mx-auto">{{ $d['subheading'] }}</p>
        @endif
        @if(!empty($d['cta_text']) && !empty($d['cta_url']))
        <div class="mt-8">
            <a href="{{ $d['cta_url'] }}" class="rounded-xl gradient-brand px-7 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 inline-block">
                {{ $d['cta_text'] }}
            </a>
        </div>
        @endif
    </div>
</section>
@endif
