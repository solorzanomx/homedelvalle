@extends('layouts.public')

@section('title', $meta['title'] ?? 'Vende tu propiedad')

@section('meta')
    <x-public.seo-meta
        :title="$meta['title'] ?? 'Vende tu propiedad'"
        :description="$meta['description'] ?? ''"
    />
@endsection

@section('content')

{{-- HERO + FORM --}}
<section class="relative overflow-hidden bg-brand-950" id="inicio">
    <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-brand-900/90 to-brand-800/80"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(59,130,196,0.15)_0%,_transparent_60%)]"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl animate-float"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Left: copy --}}
            <div x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/10 px-4 py-1.5 text-sm text-brand-200 backdrop-blur-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $campaign['badge'] ?? 'Asesoría gratuita' }}
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    {!! $campaign['heading'] ?? 'Vende tu propiedad rápido y al mejor precio' !!}
                </h1>
                <p class="mt-5 text-lg text-brand-200/80 leading-relaxed">
                    {{ $campaign['subheading'] ?? 'Conectamos tu propiedad con compradores calificados. Sin comisiones ocultas.' }}
                </p>

                {{-- Metrics --}}
                <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-6">
                    @foreach($metrics as $m)
                    <div>
                        <p class="text-2xl font-extrabold text-white">{{ $m['value'] ?? '' }}</p>
                        <p class="text-xs text-brand-300/60 mt-1">{{ $m['label'] ?? '' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: form --}}
            <div class="rounded-2xl bg-white p-8 lg:p-10 shadow-premium-xl" x-data x-intersect.once="$el.classList.add('animate-slide-in-right')">
                <h2 class="text-xl font-bold text-gray-900">Solicita tu valuación gratuita</h2>
                <p class="text-sm text-gray-500 mt-1.5 mb-6">Responderemos en menos de 24 horas.</p>

                <form method="POST" action="{{ route('landing.submit') }}" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf
                    <input type="hidden" name="utm_source" value="{{ request('utm_source') }}">
                    <input type="hidden" name="utm_medium" value="{{ request('utm_medium') }}">
                    <input type="hidden" name="utm_campaign" value="{{ request('utm_campaign') }}">
                    <div style="position:absolute;left:-9999px;" aria-hidden="true"><input type="text" name="website_url" tabindex="-1"></div>

                    <div class="space-y-4">
                        <div>
                            <input type="text" name="name" required placeholder="Tu nombre completo" value="{{ old('name') }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input type="email" name="email" required placeholder="Email" value="{{ old('email') }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                            <input type="tel" name="phone" required placeholder="Teléfono" value="{{ old('phone') }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all">
                        </div>
                        <div>
                            <textarea name="message" rows="3" placeholder="Cuéntanos sobre tu propiedad (ubicación, tipo, metros...)"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400 transition-all resize-none">{{ old('message') }}</textarea>
                        </div>

                        @php
                            try { $privacyDoc = \App\Models\LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first(); } catch (\Exception $e) { $privacyDoc = null; }
                        @endphp
                        @if($privacyDoc)
                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="accept_privacy" id="accept_privacy_vender" required class="mt-1 rounded border-gray-300">
                            <label for="accept_privacy_vender" class="text-xs text-gray-500 leading-snug">
                                Acepto el <a href="{{ route('legal.public', $privacyDoc->slug) }}" target="_blank" class="text-brand-500 underline hover:text-brand-600">Aviso de Privacidad</a>
                            </label>
                        </div>
                        @endif

                        <button type="submit" :disabled="submitting"
                            class="w-full rounded-xl gradient-brand px-6 py-3.5 text-sm font-semibold text-white shadow-brand hover:shadow-brand-lg hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 flex items-center justify-center gap-2">
                            <template x-if="!submitting"><span>Quiero mi valuación gratuita</span></template>
                            <template x-if="submitting">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Enviando...
                                </span>
                            </template>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- BENEFITS --}}
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">¿Por qué vender con nosotros?</h2>
            <p class="mt-4 text-lg text-gray-500">Pocos inmuebles. Más control. Mejores resultados.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($benefits as $bi => $b)
            <div class="text-center p-6 rounded-2xl hover:bg-brand-50/50 transition-all duration-300" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $bi * 100 }}ms">
                <div class="mx-auto flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-50 mb-5">
                    <x-icon name="shield-check" class="w-7 h-7 text-brand-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900">{{ $b['title'] ?? '' }}</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $b['desc'] ?? $b['description'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- PROCESS --}}
<section class="py-20 sm:py-24 bg-gray-50/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Proceso</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Así de fácil es vender con nosotros</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($processSteps as $si => $step)
            <div class="relative p-8 rounded-2xl bg-white border border-gray-200/80 hover:border-brand-200 hover:shadow-premium-lg transition-all duration-500"
                 x-data x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $si * 120 }}ms">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl gradient-brand text-white text-lg font-extrabold mb-6 shadow-brand">
                    {{ $step['num'] ?? str_pad($si + 1, 2, '0', STR_PAD_LEFT) }}
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ $step['title'] ?? '' }}</h3>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">{{ $step['desc'] ?? $step['description'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
@if(!empty($faqs))
<section class="py-20 sm:py-24 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Preguntas frecuentes</h2>
        </div>
        <div class="space-y-4">
            @foreach($faqs as $fi => $faq)
            <div x-data="{ open: false }" class="rounded-2xl border border-gray-200/80 overflow-hidden transition-all duration-300 hover:border-brand-200" x-intersect.once="$el.classList.add('animate-fade-in-up')" style="animation-delay: {{ $fi * 80 }}ms">
                <button @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="text-sm font-semibold text-gray-900 pr-4">{{ $faq['q'] ?? '' }}</span>
                    <span class="shrink-0 transition-transform duration-300" :class="{ 'rotate-180': open }"><x-icon name="chevron-down" class="w-5 h-5 text-gray-400" /></span>
                </button>
                <div x-show="open" x-collapse class="px-6 pb-6">
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $faq['a'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- FINAL CTA --}}
<section class="py-24 sm:py-32 bg-brand-950 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(59,130,196,0.1)_0%,_transparent_70%)]"></div>
    <div class="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center" x-data x-intersect.once="$el.classList.add('animate-fade-in-up')">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $campaign['cta_heading'] ?? '¿Listo para vender tu propiedad?' }}</h2>
        <p class="mt-5 text-lg text-brand-200/70">{{ $campaign['cta_subheading'] ?? 'Solicita tu asesoría gratuita hoy.' }}</p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="#inicio" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-4 text-sm font-bold text-brand-900 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                Solicitar valuación gratuita
            </a>
            @if($siteSettings?->whatsapp_number)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}?text={{ urlencode($campaign['wa_message'] ?? 'Hola, me interesa vender mi propiedad') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-8 py-4 text-sm font-semibold text-white hover:bg-white/10 transition-all duration-300">
                <x-icon name="brands/whatsapp" class="w-4 h-4 text-[#25D366]" />
                WhatsApp directo
            </a>
            @endif
        </div>
    </div>
</section>

@endsection
