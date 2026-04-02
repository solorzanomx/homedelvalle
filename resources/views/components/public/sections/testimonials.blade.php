@php $d = $data; $items = $d['items'] ?? []; @endphp
@if(count($items))
<section class="py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if(!empty($d['heading']))
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">{{ $d['heading'] }}</h2>
        </div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-{{ min(count($items), 3) }} gap-8">
            @foreach($items as $test)
            <div class="rounded-2xl border border-gray-200/60 p-8 hover:shadow-premium-lg transition-all duration-500">
                <svg class="w-8 h-8 text-brand-300 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                <p class="text-gray-600 leading-relaxed">{{ $test['text'] ?? '' }}</p>
                <div class="mt-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-white flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr($test['name'] ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $test['name'] ?? '' }}</p>
                        @if(!empty($test['role']))
                        <p class="text-xs text-gray-500">{{ $test['role'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
