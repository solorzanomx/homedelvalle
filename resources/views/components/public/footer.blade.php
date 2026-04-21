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
                <div class="flex items-center gap-2.5 mb-5">
                    @if($siteSettings?->logo_path_dark && $siteSettings?->logo_type === 'image')
                        <img src="{{ Storage::url($siteSettings->logo_path_dark) }}" alt="{{ $siteName }}" style="max-height:40px; max-width:200px; display:block;">
                    @else
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl gradient-brand">
                            <x-icon name="home" class="w-4.5 h-4.5 text-white" />
                        </div>
                        <span class="text-lg font-bold text-white">{{ $siteName }}</span>
                    @endif
                </div>
                <p class="text-sm leading-relaxed text-gray-500">{{ $footerAbout ?: ($siteSettings?->site_tagline ?? 'Firma inmobiliaria boutique de alta precisión en la Benito Juárez, CDMX. Pocos inmuebles. Más control. Mejores resultados.') }}</p>

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
                    <li><a href="{{ route('propiedades.index') }}" class="hover:text-brand-400 transition-colors duration-200">Propiedades</a></li>
                    <li><a href="{{ url('/servicios') }}" class="hover:text-brand-400 transition-colors duration-200">Servicios</a></li>
                    <li><a href="{{ url('/vende-tu-propiedad') }}" class="hover:text-brand-400 transition-colors duration-200">Vende tu Propiedad</a></li>
                    <li><a href="{{ route('nosotros') }}" class="hover:text-brand-400 transition-colors duration-200">Nosotros</a></li>
                    @if(Route::has('blog.index'))
                    <li><a href="{{ route('blog.index') }}" class="hover:text-brand-400 transition-colors duration-200">Blog</a></li>
                    @endif
                    <li><a href="{{ route('contacto') }}" class="hover:text-brand-400 transition-colors duration-200">Contacto</a></li>
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
