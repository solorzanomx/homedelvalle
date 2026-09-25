@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Pólizas jurídicas de arrendamiento: los 3 planes de Previsión Legal"
        description="Si rentas sin un aval con propiedad en CDMX, la póliza jurídica respalda tu renta. Conoce qué incluye cada plan —Básica, Superior e Integral— y cómo funciona el proceso con Home del Valle."
        :canonical="url('/rentar/polizas-juridicas')"
    />
@endsection

@section('content')

<x-public.hero
    heading="Pólizas jurídicas de arrendamiento"
    subheading="Si no tienes un aval con propiedad en la CDMX, una póliza jurídica respalda tu renta. Estos son los tres planes de Previsión Legal y lo que incluye cada uno."
    :breadcrumb-items="[['label' => 'Rentar', 'url' => route('landing.rentar')], ['label' => 'Pólizas jurídicas']]"
/>

{{-- Cómo funciona --}}
<section class="relative py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Cómo funciona</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Del plan a la firma, acompañado</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['01', 'Reúnes tus documentos', 'Identificación, comprobante de domicilio y comprobante de ingresos, en tu portal.'],
                ['02', 'Eliges tu plan', 'Comparas los tres planes y eliges el que mejor te acomode.'],
                ['03', 'Previsión Legal investiga', 'Ellos realizan la investigación del inquilino y emiten tu póliza. Tu asesor tramita el alta contigo.'],
                ['04', 'Contrato y firma', 'Previsión Legal emite el contrato de arrendamiento; lo revisas y lo firmas desde tu portal.'],
            ] as [$n, $title, $desc])
            <div class="p-6 rounded-2xl bg-white border border-gray-200/60">
                <div class="text-sm font-bold text-brand-500">{{ $n }}</div>
                <h3 class="mt-2 text-base font-bold text-gray-900">{{ $title }}</h3>
                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-gray-500">La póliza la pagas directamente a Previsión Legal. En Home del Valle no cobramos al inquilino por buscar, asesorar ni por tramitar tu póliza.</p>
    </div>
</section>

{{-- Los planes --}}
<section class="py-16 sm:py-20 bg-gray-50/70">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-brand-500 uppercase tracking-widest mb-3">Los tres planes</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Qué incluye cada uno</h2>
            <p class="mt-3 text-gray-500 max-w-2xl mx-auto">Cada plan suma cobertura al anterior. Lo que cambia entre ellos es qué tanto respaldo legal tiene tu contrato si algo sale mal.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($plans as $plan)
            <div class="relative flex flex-col rounded-3xl bg-white p-8 {{ $plan->is_recommended ? 'border-2 border-brand-500 shadow-xl' : 'border border-gray-200/70 shadow-sm' }}">
                @if($plan->tagline)
                <span class="absolute -top-3 left-8 rounded-full bg-brand-600 px-3 py-1 text-xs font-bold text-white">{{ $plan->tagline }}</span>
                @endif
                <h3 class="text-2xl font-extrabold text-gray-900">{{ $plan->name }}</h3>
                @if($plan->description)<p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $plan->description }}</p>@endif

                <div class="mt-5 mb-6 rounded-xl bg-brand-50/70 px-4 py-3">
                    @if($plan->show_price_public && $plan->price !== null)
                        <div class="text-2xl font-extrabold text-brand-700">{{ $plan->price_formatted }}</div>
                        <div class="text-xs text-gray-500">Tarifa de referencia; puede variar según el estado del inmueble.</div>
                    @else
                        <div class="text-sm font-semibold text-brand-800">Tarifa según el estado del inmueble</div>
                        <div class="text-xs text-gray-500">Pregúntale a tu asesor: te la cotizamos sin costo.</div>
                    @endif
                </div>

                <ul class="space-y-3 flex-1">
                    @foreach(($plan->inclusions ?? []) as $item)
                    <li class="flex items-start gap-3">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full gradient-brand text-white shrink-0 mt-0.5"><x-icon name="check" class="w-3.5 h-3.5" /></span>
                        <span class="text-sm text-gray-700 leading-relaxed {{ str_starts_with($item, 'Todo lo del plan') ? 'font-semibold text-gray-900' : '' }}">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>

                <a href="{{ route('contacto') }}" class="mt-8 inline-flex items-center justify-center gap-2 rounded-xl {{ $plan->is_recommended ? 'gradient-brand text-white shadow-lg' : 'border border-brand-200 text-brand-700 bg-white' }} px-5 py-3 text-sm font-semibold hover:-translate-y-0.5 transition-all">
                    Quiero cotizar el plan {{ $plan->name }} <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>
            @endforeach
        </div>

        <p class="mt-8 text-center text-xs text-gray-400 max-w-3xl mx-auto leading-relaxed">
            Coberturas y tarifas son de Previsión Legal y pueden cambiar según el estado donde se ubica el inmueble y las condiciones de la renta. Esta página es informativa: confirma con tu asesor las condiciones vigentes antes de contratar.
        </p>
    </div>
</section>

{{-- ¿Cuál me toca? --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mb-6">¿La póliza es para mí?</h2>
        <div class="space-y-4 text-gray-600 leading-relaxed">
            <p><strong class="text-gray-900">Si no tienes un aval con propiedad en la CDMX</strong>, tu garantía es una póliza jurídica: eliges uno de los tres planes y Previsión Legal se encarga de la investigación.</p>
            <p><strong class="text-gray-900">Si sí tienes aval con propiedad en la CDMX</strong>, el camino es la investigación de aval con Home del Valle (cuota de $3,500 MXN, no reembolsable). Revisa los <a href="{{ route('landing.rentar.requisitos') }}" class="font-semibold text-brand-600 hover:underline">requisitos para rentar</a>.</p>
        </div>

        <div class="mt-10 rounded-3xl gradient-brand px-8 py-10 text-center">
            <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">¿Cuál plan te conviene?</h3>
            <p class="mt-2 text-brand-100">Cuéntanos de tu renta y te ayudamos a elegir.</p>
            <a href="{{ route('contacto') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-lg hover:-translate-y-0.5 transition-all">Hablar con un asesor</a>
        </div>
    </div>
</section>

@endsection
