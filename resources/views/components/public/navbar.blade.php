@php
    $siteName = $siteSettings?->site_name ?? 'Home del Valle';
    $logoPath = $siteSettings?->logo_path;
    $logoType = $siteSettings?->logo_type ?? 'text';

    $navbarCtaEnabled = $siteSettings?->navbar_cta_enabled ?? true;
    $navbarCtaText    = $siteSettings?->navbar_cta_text ?? 'Hablemos';
    $navbarCtaUrl     = $siteSettings?->navbar_cta_url ?? route('contacto');
@endphp

<header x-data="{ open: false, scrolled: false, dropBuscar: false, dropPropietario: false }"
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 }, { passive: true })"
        @click.outside="dropBuscar = false; dropPropietario = false"
        :class="scrolled ? 'glass shadow-premium border-gray-200/40' : 'bg-transparent'"
        class="sticky top-0 z-40 border-b border-transparent transition-all duration-500">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[72px] items-center justify-between">

            {{-- ── Logo ── --}}
            <a href="{{ route('home') }}" class="flex flex-col items-start gap-0 shrink-0 group">
                @if($logoType === 'image' && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $siteName }}" class="h-8 w-auto transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105"
                         style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                        <x-icon name="home" class="w-5 h-5 text-white" />
                    </div>
                @endif
                <span class="hidden lg:block text-xs font-semibold text-gray-500 tracking-wide leading-tight mt-1">Mejores resultados</span>
            </a>

            {{-- ── Desktop Nav ── --}}
            <div class="hidden md:flex items-center gap-0.5">

                {{-- Dropdown: Buscar inmueble --}}
                <div class="relative" x-data>
                    <button @click="dropBuscar = !dropBuscar; dropPropietario = false"
                            :class="dropBuscar ? 'text-gray-900 bg-gray-100/70' : 'text-gray-600 hover:text-gray-900'"
                            class="relative inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium transition-all duration-200 rounded-lg">
                        Buscar inmueble
                        <x-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" ::class="dropBuscar ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="dropBuscar" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute left-0 top-full mt-2 w-72 rounded-2xl bg-white shadow-premium-xl border border-gray-100/80 overflow-hidden"
                         @click="dropBuscar = false">
                        <div class="p-2">
                            <a href="{{ route('landing.compra') }}" class="group flex items-start gap-3.5 rounded-xl p-3.5 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 group-hover:bg-brand-500 transition-colors duration-300 shrink-0 mt-0.5">
                                    <x-icon name="search" class="w-4 h-4 text-brand-500 group-hover:text-white transition-colors duration-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Comprar propiedad</p>
                                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Búsqueda curada en Benito Juárez. Solo opciones verificadas.</p>
                                </div>
                            </a>
                            <a href="{{ route('landing.rentar') }}" class="group flex items-start gap-3.5 rounded-xl p-3.5 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 group-hover:bg-brand-500 transition-colors duration-300 shrink-0 mt-0.5">
                                    <x-icon name="key" class="w-4 h-4 text-brand-500 group-hover:text-white transition-colors duration-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Rentar para vivir</p>
                                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Curación personalizada. 3–5 opciones en menos de 72 horas.</p>
                                </div>
                            </a>
                        </div>
                        <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/70">
                            <a href="{{ route('propiedades.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                                Ver todas las propiedades
                                <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Dropdown: Soy propietario --}}
                <div class="relative" x-data>
                    <button @click="dropPropietario = !dropPropietario; dropBuscar = false"
                            :class="dropPropietario ? 'text-gray-900 bg-gray-100/70' : 'text-gray-600 hover:text-gray-900'"
                            class="relative inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium transition-all duration-200 rounded-lg">
                        Soy propietario
                        <x-icon name="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" ::class="dropPropietario ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="dropPropietario" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute left-0 top-full mt-2 w-72 rounded-2xl bg-white shadow-premium-xl border border-gray-100/80 overflow-hidden"
                         @click="dropPropietario = false">
                        <div class="p-2">
                            <a href="{{ route('landing.vende') }}" class="group flex items-start gap-3.5 rounded-xl p-3.5 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 group-hover:bg-brand-500 transition-colors duration-300 shrink-0 mt-0.5">
                                    <x-icon name="home" class="w-4 h-4 text-brand-500 group-hover:text-white transition-colors duration-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Vender mi propiedad</p>
                                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Valuación gratuita y venta en 45 días promedio.</p>
                                </div>
                            </a>
                            <a href="{{ route('landing.renta-tu-propiedad') }}" class="group flex items-start gap-3.5 rounded-xl p-3.5 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-50 group-hover:bg-brand-500 transition-colors duration-300 shrink-0 mt-0.5">
                                    <x-icon name="building-2" class="w-4 h-4 text-brand-500 group-hover:text-white transition-colors duration-300" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Rentar mi inmueble</p>
                                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Inquilino calificado, póliza jurídica y administración integral.</p>
                                </div>
                            </a>
                        </div>
                        <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/70">
                            <a href="{{ route('landing.desarrolladores') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                                Soy desarrollador o inversionista
                                <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Links planos --}}
                @foreach([
                    ['label' => 'Mercado',  'url' => '/mercado'],
                    ['label' => 'Nosotros', 'url' => '/nosotros'],
                    ['label' => 'Blog',     'url' => '/blog'],
                ] as $link)
                <a href="{{ $link['url'] }}"
                   @click="dropBuscar = false; dropPropietario = false"
                   class="relative px-4 py-2 text-sm font-medium transition-all duration-300 rounded-lg group/link {{ request()->is(ltrim($link['url'], '/') ?: '/') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ $link['label'] }}
                    <span class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full scale-x-0 group-hover/link:scale-x-100 transition-all duration-300 origin-left" style="background: var(--color-primary);"></span>
                </a>
                @endforeach

            </div>

            {{-- ── CTA + Admin (desktop) ── --}}
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

            {{-- ── Mobile Hamburger ── --}}
            <button @click="open = !open; dropBuscar = false; dropPropietario = false"
                    class="md:hidden relative p-2.5 rounded-xl text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-all duration-200" aria-label="Menu">
                <span x-show="!open" x-transition><x-icon name="menu" class="w-5 h-5" /></span>
                <span x-show="open" x-cloak x-transition><x-icon name="x" class="w-5 h-5" /></span>
            </button>
        </div>

        {{-- ── Mobile Nav ── --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden border-t border-gray-100/60 py-4">

            {{-- Grupo: Buscar inmueble --}}
            <p class="px-4 pt-2 pb-1 text-[0.65rem] font-bold tracking-widest uppercase text-gray-400">Buscar inmueble</p>
            <a href="{{ route('landing.compra') }}" @click="open = false"
               class="flex items-center gap-3 rounded-xl mx-2 px-3 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                <x-icon name="search" class="w-4 h-4 text-brand-500 shrink-0" />
                Comprar propiedad
            </a>
            <a href="{{ route('landing.rentar') }}" @click="open = false"
               class="flex items-center gap-3 rounded-xl mx-2 px-3 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                <x-icon name="key" class="w-4 h-4 text-brand-500 shrink-0" />
                Rentar para vivir
            </a>

            {{-- Grupo: Soy propietario --}}
            <p class="px-4 pt-4 pb-1 text-[0.65rem] font-bold tracking-widest uppercase text-gray-400">Soy propietario</p>
            <a href="{{ route('landing.vende') }}" @click="open = false"
               class="flex items-center gap-3 rounded-xl mx-2 px-3 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                <x-icon name="home" class="w-4 h-4 text-brand-500 shrink-0" />
                Vender mi propiedad
            </a>
            <a href="{{ route('landing.renta-tu-propiedad') }}" @click="open = false"
               class="flex items-center gap-3 rounded-xl mx-2 px-3 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                <x-icon name="building-2" class="w-4 h-4 text-brand-500 shrink-0" />
                Rentar mi inmueble
            </a>

            {{-- Links planos --}}
            <div class="mt-2 border-t border-gray-100/60 pt-2">
                @foreach([
                    ['label' => 'Mercado',   'url' => '/mercado'],
                    ['label' => 'Nosotros',  'url' => '/nosotros'],
                    ['label' => 'Blog',      'url' => '/blog'],
                    ['label' => 'Contacto',  'url' => '/contacto'],
                    ['label' => 'Desarrolladores', 'url' => '/desarrolladores-e-inversionistas'],
                ] as $link)
                <a href="{{ $link['url'] }}" @click="open = false"
                   class="block rounded-xl mx-2 px-3 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                    {{ $link['label'] }}
                </a>
                @endforeach
            </div>

            {{-- CTA (mobile) --}}
            <div class="border-t border-gray-100/60 pt-3 mt-2 mx-2 space-y-2">
                @if($navbarCtaEnabled)
                <a href="{{ $navbarCtaUrl }}" @click="open = false"
                   class="block rounded-xl px-4 py-3.5 text-base font-semibold text-white text-center shadow-lg transition-all duration-200"
                   style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));">
                    {{ $navbarCtaText }}
                </a>
                @endif
                @auth
                <a href="{{ route('admin.dashboard') }}" @click="open = false"
                   class="block rounded-xl px-4 py-3 text-base font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-all duration-200 text-center">
                    <x-icon name="settings" class="w-4 h-4 inline-block mr-1.5 -mt-0.5" />
                    Office
                </a>
                @endauth
            </div>
        </div>
    </nav>
</header>
