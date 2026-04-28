@php
    $siteName = $siteSettings?->site_name ?? 'Home del Valle';
    $logoPath = $siteSettings?->logo_path;
    $logoType = $siteSettings?->logo_type ?? 'text';

    // Orden persuasivo: interés → validación → confianza → conversión
    $defaultLinks = [
        ['label' => 'Propiedades', 'url' => '/propiedades'],
        ['label' => 'Precios de Mercado', 'url' => '/mercado'],
        ['label' => 'Servicios', 'url' => '/servicios'],
        ['label' => 'Nosotros', 'url' => '/nosotros'],
        ['label' => 'Testimonios', 'url' => '/testimonios'],
        ['label' => 'Guía Inmobiliaria', 'url' => '/blog'],
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
            {{-- Logo + Slogan --}}
            <a href="{{ route('home') }}" class="flex flex-col items-start gap-0 shrink-0 group">
                @if($logoType === 'image' && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $siteName }}" class="h-8 w-auto transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105"
                         style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        <x-icon name="home" class="w-5 h-5 text-white" />
                    </div>
                @endif
                <span class="hidden lg:block text-xs font-semibold text-gray-500 tracking-wide leading-tight mt-1">Pocos inmuebles. Más control.</span>
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
                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
                @endif
                @auth
                <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all duration-200" title="Office">
                    <x-icon name="settings" class="w-4.5 h-4.5" />
                </a>
                @endauth
            </div>

            {{-- Mobile Hamburger --}}
            <button @click="open = !open" class="md:hidden relative p-2.5 rounded-xl text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-all duration-200" aria-label="Menu">
                <span x-show="!open" x-transition><x-icon name="menu" class="w-5 h-5" /></span>
                <span x-show="open" x-cloak x-transition><x-icon name="x" class="w-5 h-5" /></span>
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
                    <x-icon name="settings" class="w-4 h-4 inline-block mr-1.5 -mt-0.5" />
                    Office
                </a>
                @endauth
            </div>
        </div>
    </nav>
</header>
