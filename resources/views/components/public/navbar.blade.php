@php
    $siteName = $siteSettings?->site_name ?? 'Home del Valle';
    $logoPath = $siteSettings?->logo_path;
    $logoType = $siteSettings?->logo_type ?? 'text';

    // Orden persuasivo: interes → validacion → confianza → conversion
    $defaultLinks = [
        ['label' => 'Propiedades', 'url' => '/propiedades'],
        ['label' => 'Servicios', 'url' => '/servicios'],
        ['label' => 'Nosotros', 'url' => '/nosotros'],
        ['label' => 'Guia Inmobiliaria', 'url' => '/blog'],
        ['label' => 'Contacto', 'url' => '/contacto'],
    ];

    $navbarCtaEnabled = $siteSettings?->navbar_cta_enabled ?? true;
    $navbarCtaText = $siteSettings?->navbar_cta_text ?? 'Vende tu propiedad';
    $navbarCtaUrl = $siteSettings?->navbar_cta_url ?? '/vende-tu-propiedad';
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

            {{-- Desktop Nav — always use $defaultLinks (ignore DB menus to avoid duplicates) --}}
            <div class="hidden md:flex items-center gap-0.5">
                @foreach($defaultLinks as $link)
                    <a href="{{ $link['url'] }}"
                       class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link {{ request()->is(ltrim($link['url'], '/') ?: '/') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ $link['label'] }}
                        <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full scale-x-0 group-hover/link:scale-x-100 transition-all duration-300 origin-left" style="background: var(--color-primary);"></span>
                    </a>
                @endforeach
            </div>

            {{-- CTA button (desktop) --}}
            <div class="hidden md:flex items-center gap-2 ml-4">
                @if($navbarCtaEnabled)
                <a href="{{ $navbarCtaUrl }}"
                   class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300"
                   style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                    {{ $navbarCtaText }}
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                @endif
                @auth
                <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all duration-200" title="Office">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
            @foreach($defaultLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="block rounded-xl px-4 py-3 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                    {{ $link['label'] }}
                </a>
            @endforeach

            {{-- CTA (mobile) --}}
            <div class="border-t border-gray-100/60 pt-3 mt-2 space-y-2">
                @if($navbarCtaEnabled)
                <a href="{{ $navbarCtaUrl }}"
                   class="block rounded-xl px-4 py-3.5 text-base font-semibold text-white text-center shadow-lg transition-all duration-200"
                   style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                    {{ $navbarCtaText }}
                </a>
                @endif
                @auth
                <a href="{{ route('admin.dashboard') }}"
                   class="block rounded-xl px-4 py-3 text-base font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-all duration-200 text-center">
                    <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Office
                </a>
                @endauth
            </div>
        </div>
    </nav>
</header>
