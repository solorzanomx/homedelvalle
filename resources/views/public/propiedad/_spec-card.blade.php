<div class="rounded-2xl border border-gray-200/60 bg-brand-50/30 p-5 text-center transition-all duration-300 hover:shadow-premium hover:border-brand-100" style="animation-delay: {{ $delay ?? 0 }}ms">
    @switch($spec['icon'])
        @case('bed')
            <svg class="w-6 h-6 text-brand-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            @break
        @case('bath')
            <svg class="w-6 h-6 text-brand-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
            @break
        @case('area')
            <svg class="w-6 h-6 text-brand-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            @break
        @case('car')
            <svg class="w-6 h-6 text-brand-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 16h22M6 16v4m10-4v4"/></svg>
            @break
    @endswitch
    <p class="text-lg font-extrabold text-gray-900">{{ $spec['value'] }}</p>
    <p class="text-xs text-gray-500 font-medium">{{ $spec['label'] }}</p>
</div>
