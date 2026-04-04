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
                                <x-icon name="phone" class="w-5 h-5 text-brand-500" />
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
                                <x-icon name="mail" class="w-5 h-5 text-brand-500" />
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
                                <x-icon name="brands/whatsapp" class="w-5 h-5 text-[#25D366]" />
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
                                <x-icon name="map-pin" class="w-5 h-5 text-brand-500" />
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
                            <x-icon name="clock" class="w-5 h-5 text-brand-500 mx-auto mb-1.5" />
                            <p class="text-xs font-semibold text-gray-700">Respuesta en &lt;24 hrs</p>
                        </div>
                        <div class="text-center p-3 rounded-xl bg-gray-50/60">
                            <x-icon name="shield-check" class="w-5 h-5 text-brand-500 mx-auto mb-1.5" />
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
