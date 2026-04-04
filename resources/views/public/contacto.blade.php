@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Contacto"
        description="Contáctanos para asesoría inmobiliaria personalizada en Ciudad de México. Estamos aquí para ayudarte."
        :canonical="route('contacto')"
    />
@endsection

@section('content')
    <x-public.hero heading="{{ $siteSettings?->contact_heading ?? 'Hablemos de tu propiedad' }}" subheading="{{ $siteSettings?->contact_subheading ?? 'Asesoría inmobiliaria personalizada en la Benito Juárez. Respondemos en menos de 24 horas.' }}"
        :breadcrumb-items="[['label' => 'Contacto']]" />

    <section class="py-20 sm:py-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                {{-- Contact info --}}
                <div x-data x-intersect.once="$el.classList.add('animate-slide-in-left')">
                    <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Información</p>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Información de contacto</h2>
                    <p class="mt-3 text-gray-500 leading-relaxed">Elige el medio que prefieras para comunicarte con nosotros.</p>

                    <div class="mt-8 space-y-4">
                        @if($siteSettings?->contact_phone)
                        <a href="tel:{{ $siteSettings->contact_phone }}" class="flex items-center gap-4 rounded-2xl border border-gray-200/60 p-5 hover:bg-brand-50/30 hover:border-brand-100 transition-all duration-300 group">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 group-hover:bg-brand-500/15 transition-colors duration-300 shrink-0">
                                <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Teléfono</p>
                                <p class="text-sm text-gray-500 group-hover:text-brand-600 transition-colors duration-200">{{ $siteSettings->contact_phone }}</p>
                            </div>
                        </a>
                        @endif

                        @if($siteSettings?->contact_email)
                        <a href="mailto:{{ $siteSettings->contact_email }}" class="flex items-center gap-4 rounded-2xl border border-gray-200/60 p-5 hover:bg-brand-50/30 hover:border-brand-100 transition-all duration-300 group">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 group-hover:bg-brand-500/15 transition-colors duration-300 shrink-0">
                                <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Email</p>
                                <p class="text-sm text-gray-500 group-hover:text-brand-600 transition-colors duration-200">{{ $siteSettings->contact_email }}</p>
                            </div>
                        </a>
                        @endif

                        @if($siteSettings?->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-4 rounded-2xl border border-gray-200/60 p-5 hover:bg-[#25D366]/5 hover:border-[#25D366]/20 transition-all duration-300 group">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#25D366]/10 group-hover:bg-[#25D366]/15 transition-colors duration-300 shrink-0">
                                <svg class="w-5 h-5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">WhatsApp</p>
                                <p class="text-sm text-gray-500 group-hover:text-[#25D366] transition-colors duration-200">Envíanos un mensaje</p>
                            </div>
                        </a>
                        @endif

                        @if($siteSettings?->address)
                        <div class="flex items-center gap-4 rounded-2xl border border-gray-200/60 p-5">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500/10 shrink-0">
                                <svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Dirección</p>
                                <p class="text-sm text-gray-500">{{ $siteSettings->address }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Contact form --}}
                <div x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                    <div class="rounded-2xl border border-gray-200/60 p-8 shadow-premium-lg">
                        <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Formulario</p>
                        <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Envíanos un mensaje</h2>
                        <p class="mt-2 text-gray-500 mb-8">Completa el formulario y te contactaremos lo antes posible.</p>
                        <x-public.contact-form />
                    </div>

                    {{-- Trust signals --}}
                    <div class="mt-6 grid grid-cols-3 gap-4">
                        <div class="text-center p-3 rounded-xl bg-gray-50/60">
                            <svg class="w-5 h-5 text-brand-500 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs font-semibold text-gray-700">Respuesta en &lt;24 hrs</p>
                        </div>
                        <div class="text-center p-3 rounded-xl bg-gray-50/60">
                            <svg class="w-5 h-5 text-brand-500 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <p class="text-xs font-semibold text-gray-700">Sin compromiso</p>
                        </div>
                        <div class="text-center p-3 rounded-xl bg-gray-50/60">
                            <svg class="w-5 h-5 text-brand-500 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            <p class="text-xs font-semibold text-gray-700">Asesoría gratuita</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Google Maps --}}
    @if($siteSettings?->google_maps_embed)
    <section class="pb-20 sm:pb-24 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <div class="rounded-2xl overflow-hidden border border-gray-200/60 shadow-premium aspect-[16/6]">
                {!! $siteSettings->google_maps_embed !!}
            </div>
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section class="py-24 sm:py-32 gradient-brand-soft" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Tienes una propiedad en la Benito Juárez?</h2>
            <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Conoce cuánto vale tu inmueble con una valuación profesional gratuita y sin compromiso.</p>
            <div class="mt-8">
                <a href="{{ route('landing.vende') }}" class="rounded-xl gradient-brand px-7 py-4 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                    Valúa tu propiedad
                </a>
            </div>
        </div>
    </section>
@endsection
