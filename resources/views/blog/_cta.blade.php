@php $cta = $cta ?? null; @endphp
@if($cta && !empty($cta['title']))
<div class="not-prose my-10">
    <div class="relative rounded-2xl overflow-hidden" style="background: linear-gradient(135deg, #f0f5fa 0%, #f8fafc 100%);">
        <div style="position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--color-primary, #3B82C4);"></div>
        <div class="p-8 sm:p-10 pl-10 sm:pl-12">
            <div style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:12px;background:var(--color-primary, #3B82C4);margin-bottom:1rem;">
                <svg class="w-5 h-5" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight">{{ $cta['title'] }}</h3>
            @if(!empty($cta['description']))
                <p class="mt-2 text-gray-500 leading-relaxed max-w-2xl text-base">{{ $cta['description'] }}</p>
            @endif
            @if(!empty($cta['button_text']) && !empty($cta['link']))
                <a href="{{ $cta['link'] }}"
                   class="mt-5 inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold transition-all duration-300 hover:-translate-y-0.5"
                   style="background: var(--color-primary, #3B82C4); box-shadow: 0 4px 14px rgba(59,130,196,0.3);">
                    {{ $cta['button_text'] }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            @endif
        </div>
    </div>
</div>
@endif
