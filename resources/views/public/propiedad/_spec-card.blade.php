<div class="rounded-2xl border border-gray-200/60 bg-brand-50/30 p-5 text-center transition-all duration-300 hover:shadow-premium hover:border-brand-100" style="animation-delay: {{ $delay ?? 0 }}ms">
    @switch($spec['icon'])
        @case('bed')
            <x-icon name="bed-double" class="w-6 h-6 text-brand-500 mx-auto mb-2" />
            @break
        @case('bath')
            <x-icon name="bath" class="w-6 h-6 text-brand-500 mx-auto mb-2" />
            @break
        @case('area')
            <x-icon name="maximize" class="w-6 h-6 text-brand-500 mx-auto mb-2" />
            @break
        @case('car')
            <x-icon name="car" class="w-6 h-6 text-brand-500 mx-auto mb-2" />
            @break
    @endswitch
    <p class="text-lg font-extrabold text-gray-900">{{ $spec['value'] }}</p>
    <p class="text-xs text-gray-500 font-medium">{{ $spec['label'] }}</p>
</div>
