@php $d = $data; $items = $d['items'] ?? []; @endphp
@if(count($items))
<section class="py-16 sm:py-20 bg-gray-50/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if(!empty($d['heading']))
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $d['heading'] }}</h2>
        </div>
        @endif
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min(count($items), 4) }} gap-6">
            @foreach($items as $card)
            <div class="rounded-2xl border border-gray-200/60 bg-white p-8 hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-1 transition-all duration-500">
                @if(!empty($card['icon']))
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 mb-5 text-xl">
                    {!! $card['icon'] !!}
                </div>
                @endif
                <h3 class="text-lg font-bold text-gray-900">{{ $card['title'] ?? '' }}</h3>
                @if(!empty($card['description']))
                <p class="mt-2 text-gray-500 leading-relaxed">{{ $card['description'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
