@props(['items'])

<nav aria-label="Breadcrumb" class="mb-6">
    <ol class="flex items-center gap-1.5 text-sm text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-gray-900 transition-colors">Inicio</a></li>
        @foreach($items as $item)
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-gray-900 transition-colors">{{ $item['label'] }}</a>
                @else
                    <span class="text-gray-900 font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<x-public.json-ld type="BreadcrumbList" :data="[
    'itemListElement' => collect($items)->map(fn($item, $i) => [
        '@type' => 'ListItem',
        'position' => $i + 2,
        'name' => $item['label'],
        'item' => $item['url'] ?? request()->url(),
    ])->prepend([
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Inicio',
        'item' => route('home'),
    ])->all()
]" />
