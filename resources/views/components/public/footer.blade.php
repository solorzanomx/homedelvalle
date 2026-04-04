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
                    <div class="flex items-center justify-center w-9 h-9 rounded-xl gradient-brand">
                        <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="text-lg font-bold text-white">{{ $siteName }}</span>
                </div>
                <p class="text-sm leading-relaxed text-gray-500">{{ $footerAbout ?: ($siteSettings?->site_tagline ?? 'Firma inmobiliaria boutique de alta precisión en la Benito Juárez, CDMX. Pocos inmuebles. Más control. Mejores resultados.') }}</p>

                {{-- Social --}}
                <div class="flex gap-2.5 mt-6">
                    @if($facebook)
                    <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-brand-500/20 text-gray-500 hover:text-brand-400 transition-all duration-300" aria-label="Facebook">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    @endif
                    @if($instagram)
                    <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-brand-500/20 text-gray-500 hover:text-brand-400 transition-all duration-300" aria-label="Instagram">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    @endif
                    @if($tiktok)
                    <a href="{{ $tiktok }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-brand-500/20 text-gray-500 hover:text-brand-400 transition-all duration-300" aria-label="TikTok">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    </a>
                    @endif
                    @if($siteSettings?->whatsapp_number)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-[#25D366]/20 text-gray-500 hover:text-[#25D366] transition-all duration-300" aria-label="WhatsApp">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
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
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <a href="tel:{{ $phone }}" class="hover:text-brand-400 transition-colors duration-200">{{ $phone }}</a>
                    </li>
                    @endif
                    @if($email)
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <a href="mailto:{{ $email }}" class="hover:text-brand-400 transition-colors duration-200">{{ $email }}</a>
                    </li>
                    @endif
                    @if($address)
                    <li class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
