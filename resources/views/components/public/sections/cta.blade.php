@php $d = $data; @endphp
@if(!empty($d['heading']))
<section class="py-24 sm:py-32 gradient-brand-soft">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $d['heading'] }}</h2>
        @if(!empty($d['subheading']))
        <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">{{ $d['subheading'] }}</p>
        @endif
        @if(!empty($d['btn_text']) && !empty($d['btn_url']))
        <div class="mt-8">
            <a href="{{ $d['btn_url'] }}" class="rounded-xl gradient-brand px-7 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 inline-block">
                {{ $d['btn_text'] }}
            </a>
        </div>
        @endif
    </div>
</section>
@endif
