@php
    $siteName = $siteSettings?->site_name ?? 'Home del Valle';
    $phone = $siteSettings?->contact_phone;
    $email = $siteSettings?->contact_email;
    $address = $siteSettings?->address;
    $facebook = $siteSettings?->facebook_url;
    $instagram = $siteSettings?->instagram_url;
    $tiktok = $siteSettings?->tiktok_url;
    $footerAbout = $siteSettings?->footer_about;
    $footerBottomText = $siteSettings?->footer_bottom_text;
    $footerBottomLinks = $siteSettings?->footer_bottom_links ?? [];
    $useFooterMenu = !empty($footerMenu) && $footerMenu->items && $footerMenu->items->count();
@endphp

<footer class="bg-brand-950 text-gray-400">
    {{-- Main --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
            {{-- Brand --}}
            <div class="lg:pr-8">
                <div class="flex items-center gap-2.5 mb-3">
                    @if($siteSettings?->logo_path_dark && $siteSettings?->logo_type === 'image')
                        <img src="{{ Storage::url($siteSettings->logo_path_dark) }}" alt="{{ $siteName }}" style="max-height:40px; max-width:200px; display:block;">
                    @else
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl gradient-brand">
                            <x-icon name="home" class="w-4.5 h-4.5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">{{ $siteName }}</span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-brand-400 tracking-wide mb-5">Pocos inmuebles. Más control. Mejores resultados.</p>
                <p class="text-sm leading-relaxed text-gray-500">{{ $footerAbout ?: ($siteSettings?->site_tagline ?? 'Firma inmobiliaria boutique de alta precisión en la Benito Juárez, CDMX.') }}</p>

                {{-- Social --}}
                <div class="flex gap-2.5 mt-6">
                    @if($facebook)
                    <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-brand-500/20 text-gray-500 hover:text-brand-400 transition-all duration-300" aria-label="Facebook">
                        <x-icon name="brands/facebook" class="w-4.5 h-4.5" />
                    </a>
                    @endif
                    @if($instagram)
                    <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-brand-500/20 text-gray-500 hover:text-brand-400 transition-all duration-300" aria-label="Instagram">
                        <x-icon name="brands/instagram" class="w-4.5 h-4.5" />
                    </a>
                    @endif
                    @if($tiktok)
                    <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-brand-500/20 text-gray-500 hover:text-brand-400 transition-all duration-300" aria-label="TikTok">
                        <x-icon name="brands/tiktok" class="w-4.5 h-4.5" />
                    </a>
                    @endif
                    @if($siteSettings?->whatsapp_number)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-[#25D366]/20 text-gray-500 hover:text-[#25D366] transition-all duration-300" aria-label="WhatsApp">
                        <x-icon name="brands/whatsapp" class="w-4.5 h-4.5" />
                    </a>
                    @endif
                </div>
            </div>

            {{-- Links --}}
            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-widest mb-5">Explorar</h4>
                <ul class="space-y-3 text-sm">
                    @if($useFooterMenu)
                        @foreach($footerMenu->items->whereNull('parent_id') as $item)
                        <li><a href="{{ $item->resolveUrl() }}" class="hover:text-brand-400 transition-colors duration-200" @if($item->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $item->label }}</a></li>
                        @endforeach
                    @else
                    <li><a href="{{ url('/comprar') }}" class="hover:text-brand-400 transition-colors duration-200">Comprar</a></li>
                    <li><a href="{{ url('/vende-tu-propiedad') }}" class="hover:text-brand-400 transition-colors duration-200">Vender</a></li>
                    <li><a href="{{ url('/desarrolladores-e-inversionistas') }}" class="hover:text-brand-400 transition-colors duration-200">Inversión & Desarrollo</a></li>
                    <li><a href="{{ url('/mercado') }}" class="hover:text-brand-400 transition-colors duration-200">Precios de Mercado</a></li>
                    <li><a href="{{ url('/servicios') }}" class="hover:text-brand-400 transition-colors duration-200">Servicios</a></li>
                    <li><a href="{{ route('nosotros') }}" class="hover:text-brand-400 transition-colors duration-200">Nosotros</a></li>
                    @if(Route::has('blog.index'))
                    <li><a href="{{ route('blog.index') }}" class="hover:text-brand-400 transition-colors duration-200">Blog</a></li>
                    @endif
                    @endif
                </ul>
            </div>

            {{-- Services --}}
            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-widest mb-5">Servicios</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ url('/servicios#desarrollo-inmobiliario') }}" class="hover:text-brand-400 transition-colors duration-200">Desarrollo Inmobiliario</a></li>
                    <li><a href="{{ url('/servicios#corretaje-premium') }}" class="hover:text-brand-400 transition-colors duration-200">Corretaje Premium</a></li>
                    <li><a href="{{ url('/servicios#administracion') }}" class="hover:text-brand-400 transition-colors duration-200">Administración</a></li>
                    <li><a href="{{ url('/servicios#legal-gestoria') }}" class="hover:text-brand-400 transition-colors duration-200">Legal y Gestoría</a></li>
                    <li><a href="{{ url('/servicios#property-transformation') }}" class="hover:text-brand-400 transition-colors duration-200">Property Transformation</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-widest mb-5">Contacto</h4>
                <ul class="space-y-3.5 text-sm">
                    @if($phone)
                    <li class="flex items-start gap-2.5">
                        <x-icon name="phone" class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" />
                        <a href="tel:{{ $phone }}" class="hover:text-brand-400 transition-colors duration-200">{{ $phone }}</a>
                    </li>
                    @endif
                    @if($email)
                    <li class="flex items-start gap-2.5">
                        <x-icon name="mail" class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" />
                        <a href="mailto:{{ $email }}" class="hover:text-brand-400 transition-colors duration-200">{{ $email }}</a>
                    </li>
                    @endif
                    @if($address)
                    <li class="flex items-start gap-2.5">
                        <x-icon name="map-pin" class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" />
                        <span>{{ $address }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Trust badges --}}
    <div class="border-t border-white/5">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 flex flex-wrap items-center justify-center gap-4">

            {{-- SSL Seguro --}}
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div class="leading-tight">
                    <div class="text-xs font-semibold text-white">Sitio Seguro</div>
                    <div class="text-[10px] text-gray-500">Conexión HTTPS cifrada</div>
                </div>
            </div>

            {{-- AMPI --}}
            <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <div class="leading-tight">
                    <div class="text-xs font-semibold text-white">Miembro AMPI</div>
                    <div class="text-[10px] text-gray-500">Asociación Mexicana de Profesionales Inmobiliarios</div>
                </div>
            </div>

        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-white/5">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-center gap-3 text-xs text-gray-600">
            <span>&copy; {{ date('Y') }} {{ $siteName }} Bienes Raíces. Todos los derechos reservados.</span>
            <div class="flex items-center gap-4">
                <a href="{{ url('/legal/aviso-de-privacidad') }}" class="hover:text-gray-400 transition-colors duration-200">Aviso de privacidad</a>
                <a href="{{ url('/legal/terminos-y-condiciones') }}" class="hover:text-gray-400 transition-colors duration-200">Términos y condiciones</a>
                <a href="{{ url('/legal/politica-de-cookies') }}" class="hover:text-gray-400 transition-colors duration-200">Política de cookies</a>
                <a href="{{ route('login') }}" class="hover:text-gray-400 transition-colors duration-200">Acceso (Office)</a>
            </div>
        </div>
    </div>
</footer>
