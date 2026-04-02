@php
    $siteName = $siteSettings?->site_name ?? 'Home del Valle';
    $logoPath = $siteSettings?->logo_path;
    $logoType = $siteSettings?->logo_type ?? 'text';
    // Use dynamic menu if it has items, otherwise fall back to Page navItems
    $useHeaderMenu = isset($headerMenu) && $headerMenu && $headerMenu->items->count();
@endphp

<header x-data="{ open: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 }, { passive: true })"
        :class="scrolled ? 'glass shadow-premium border-gray-200/40' : 'bg-transparent'"
        class="sticky top-0 z-40 border-b border-transparent transition-all duration-500">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[72px] items-center justify-between">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
                @if($logoType === 'image' && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $siteName }}" class="h-9 w-auto transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl gradient-brand shadow-brand/20 group-hover:shadow-brand transition-all duration-300 group-hover:scale-105">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight" :class="scrolled ? 'text-brand-700' : 'text-brand-700'">{{ $siteName }}</span>
                @endif
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-0.5">
                @if($useHeaderMenu)
                    @foreach($headerMenu->items as $menuItem)
                        @if($menuItem->style === 'button')
                            <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                               class="ml-4 inline-flex items-center gap-2 rounded-xl gradient-brand px-6 py-2.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                                {{ $menuItem->label }}
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @elseif($menuItem->style === 'muted')
                            <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                               class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg text-gray-400 hover:text-gray-500">
                                {{ $menuItem->label }}
                            </a>
                        @else
                            <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                               class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link text-gray-600 hover:text-brand-600">
                                {{ $menuItem->label }}
                                <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full bg-brand-500 scale-x-0 group-hover/link:scale-x-100 transition-all duration-300 origin-left"></span>
                            </a>
                        @endif
                    @endforeach
                @else
                    @foreach($navItems as $item)
                        @if($item->nav_style === 'button')
                            <a href="{{ $item->navHref() }}"
                               class="ml-4 inline-flex items-center gap-2 rounded-xl gradient-brand px-6 py-2.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                                {{ $item->nav_label ?: $item->title }}
                                <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @elseif($item->nav_style === 'muted')
                            <a href="{{ $item->navHref() }}"
                               class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg {{ $item->isActive() ? 'text-brand-600' : 'text-gray-400 hover:text-gray-500' }}">
                                {{ $item->nav_label ?: $item->title }}
                            </a>
                        @else
                            <a href="{{ $item->navHref() }}"
                               class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link {{ $item->isActive() ? 'text-brand-600' : 'text-gray-600 hover:text-brand-600' }}">
                                {{ $item->nav_label ?: $item->title }}
                                <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full transition-all duration-300 {{ $item->isActive() ? 'bg-brand-500 scale-x-100' : 'bg-brand-500 scale-x-0 group-hover/link:scale-x-100' }} origin-left"></span>
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- Mobile Hamburger --}}
            <button @click="open = !open" class="md:hidden relative p-2.5 rounded-xl text-gray-500 hover:text-brand-600 hover:bg-brand-50/80 transition-all duration-200" aria-label="Menu">
                <svg x-show="!open" x-transition class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak x-transition class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Mobile Nav --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden border-t border-gray-100/60 py-5 space-y-1.5">
            @if($useHeaderMenu)
                @foreach($headerMenu->items as $menuItem)
                    @if($menuItem->style === 'button')
                        <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                           class="block rounded-xl px-4 py-3.5 text-base font-semibold text-white gradient-brand text-center shadow-brand transition-all duration-200 hover:shadow-brand-lg mt-3">
                            {{ $menuItem->label }}
                        </a>
                    @else
                        <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                           class="block rounded-xl px-4 py-3 text-base font-medium text-gray-700 hover:bg-brand-50/60 hover:text-brand-600 transition-all duration-200">
                            {{ $menuItem->label }}
                        </a>
                    @endif
                @endforeach
            @else
                @foreach($navItems as $item)
                    @if($item->nav_style === 'button')
                        <a href="{{ $item->navHref() }}"
                           class="block rounded-xl px-4 py-3.5 text-base font-semibold text-white gradient-brand text-center shadow-brand transition-all duration-200 hover:shadow-brand-lg mt-3">
                            {{ $item->nav_label ?: $item->title }}
                        </a>
                    @elseif($item->nav_style === 'muted')
                        <a href="{{ $item->navHref() }}"
                           class="block rounded-xl px-4 py-3 text-sm font-medium text-gray-400 hover:bg-gray-50 text-center transition-colors">
                            {{ $item->nav_label ?: $item->title }}
                        </a>
                    @else
                        <a href="{{ $item->navHref() }}"
                           class="block rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ $item->isActive() ? 'text-brand-600 bg-brand-50/80' : 'text-gray-700 hover:bg-brand-50/60 hover:text-brand-600' }}">
                            {{ $item->nav_label ?: $item->title }}
                        </a>
                    @endif
                @endforeach
            @endif
        </div>
    </nav>
</header>
