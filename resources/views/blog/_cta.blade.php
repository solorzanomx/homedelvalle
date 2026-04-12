@php $cta = $cta ?? null; @endphp
@if($cta && !empty($cta['title']))
<div class="not-prose my-10">
    <div class="relative rounded-2xl border border-brand-200 bg-gradient-to-br from-brand-50 to-white p-8 sm:p-10 overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-brand-100 rounded-full -translate-y-1/2 translate-x-1/2 opacity-50"></div>
        <div class="relative">
            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">{{ $cta['title'] }}</h3>
            @if(!empty($cta['description']))
                <p class="mt-3 text-gray-600 leading-relaxed max-w-2xl">{{ $cta['description'] }}</p>
            @endif
            @if(!empty($cta['button_text']) && !empty($cta['link']))
                <a href="{{ $cta['link'] }}"
                   class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                   style="background: var(--color-primary, #3B82C4);">
                    {{ $cta['button_text'] }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            @endif
        </div>
    </div>
</div>
@endif
