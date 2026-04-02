@php
    $siteName = $siteSettings?->site_name ?? 'Home del Valle';
    $logoPath = $siteSettings?->logo_path;
    $logoType = $siteSettings?->logo_type ?? 'text';
    $useHeaderMenu = isset($headerMenu) && $headerMenu && method_exists($headerMenu, 'items') ? $headerMenu->items->count() : false;
    $useNavItems = isset($navItems) && $navItems && $navItems->count();
    $hasMenu = $useHeaderMenu || $useNavItems;

    // Fallback menu when DB is empty
    $defaultLinks = [
        ['label' => 'Inicio', 'url' => '/'],
        ['label' => 'Propiedades', 'url' => '/propiedades'],
        ['label' => 'Nosotros', 'url' => '/nosotros'],
        ['label' => 'Blog', 'url' => '/blog'],
        ['label' => 'Contacto', 'url' => '/contacto'],
    ];
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
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105"
                         style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-gray-800">{{ $siteName }}</span>
                @endif
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-0.5">
                @if($useHeaderMenu)
                    @foreach($headerMenu->items as $menuItem)
                        @if($menuItem->style === 'button')
                            <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                               class="ml-4 inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"
                               style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                                {{ $menuItem->label }}
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @else
                            <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                               class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link text-gray-600 hover:text-gray-900">
                                {{ $menuItem->label }}
                                <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full scale-x-0 group-hover/link:scale-x-100 transition-all duration-300 origin-left" style="background: var(--color-primary);"></span>
                            </a>
                        @endif
                    @endforeach
                @elseif($useNavItems)
                    @foreach($navItems as $item)
                        @if($item->nav_style === 'button')
                            <a href="{{ $item->navHref() }}"
                               class="ml-4 inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"
                               style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                                {{ $item->nav_label ?: $item->title }}
                            </a>
                        @else
                            <a href="{{ $item->navHref() }}"
                               class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link {{ $item->isActive() ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                                {{ $item->nav_label ?: $item->title }}
                                <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full transition-all duration-300 origin-left {{ $item->isActive() ? 'scale-x-100' : 'scale-x-0 group-hover/link:scale-x-100' }}" style="background: var(--color-primary);"></span>
                            </a>
                        @endif
                    @endforeach
                @else
                    {{-- Fallback: default links --}}
                    @foreach($defaultLinks as $link)
                        <a href="{{ $link['url'] }}"
                           class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link {{ request()->is(ltrim($link['url'], '/') ?: '/') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                            {{ $link['label'] }}
                            <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full scale-x-0 group-hover/link:scale-x-100 transition-all duration-300 origin-left" style="background: var(--color-primary);"></span>
                        </a>
                    @endforeach
                @endif
            </div>

            {{-- Auth button (desktop) --}}
            <div class="hidden md:flex items-center gap-3 ml-4">
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"
                       style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Office
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"
                       style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        Office
                    </a>
                @endauth
            </div>

            {{-- Mobile Hamburger --}}
            <button @click="open = !open" class="md:hidden relative p-2.5 rounded-xl text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-all duration-200" aria-label="Menu">
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
                    <a href="{{ $menuItem->resolveUrl() }}" target="{{ $menuItem->target }}"
                       class="block rounded-xl px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                        {{ $menuItem->label }}
                    </a>
                @endforeach
            @elseif($useNavItems)
                @foreach($navItems as $item)
                    <a href="{{ $item->navHref() }}"
                       class="block rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ $item->isActive() ? 'text-gray-900 bg-gray-50' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                        {{ $item->nav_label ?: $item->title }}
                    </a>
                @endforeach
            @else
                {{-- Fallback: default links --}}
                @foreach($defaultLinks as $link)
                    <a href="{{ $link['url'] }}"
                       class="block rounded-xl px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            @endif

            {{-- Auth link (mobile) --}}
            <div class="border-t border-gray-100/60 pt-3 mt-2">
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       class="block rounded-xl px-4 py-3.5 text-base font-semibold text-white text-center shadow-lg mt-1 transition-all duration-200"
                       style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        Office
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="block rounded-xl px-4 py-3.5 text-base font-semibold text-white text-center shadow-lg mt-1 transition-all duration-200"
                       style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        Office
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>
