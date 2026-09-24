@extends('layouts.public')

@section('title', 'Requisitos para Rentar')

@section('content')

@php
    $heading = 'Requisitos para Rentar';
    $subheading = 'Esto es lo que necesitamos para calificarte como inquilino y avanzar tu proceso de renta con Home del Valle.';

    $groups = [
        [
            'title' => 'Identificación y domicilio',
            'items' => \App\Support\TenantDocumentChecklist::IDENTIFICACION,
        ],
        [
            'title' => 'Datos laborales y comprobación de ingresos',
            'items' => \App\Support\TenantDocumentChecklist::DATOS_LABORALES + \App\Support\TenantDocumentChecklist::INGRESOS,
            'note' => 'De los comprobantes de ingresos, con un solo tipo es suficiente — se piden los últimos 3.',
        ],
        [
            'title' => 'Referencias y Buró de Crédito',
            'items' => \App\Support\TenantDocumentChecklist::REFERENCIAS + \App\Support\TenantDocumentChecklist::CREDITO,
        ],
        [
            'title' => 'Cuota de investigación',
            'items' => \App\Support\TenantDocumentChecklist::PAGO_INVESTIGACION,
            'note' => 'Tu asesor confirma el pago y tu recibo queda disponible para descarga en tu portal.',
        ],
    ];
@endphp

<x-public.seo-meta
    :title="$heading"
    :description="$subheading"
/>

<x-public.hero
    :heading="$heading"
    :subheading="$subheading"
    :breadcrumb-items="[['label' => 'Rentar', 'url' => route('landing.rentar')], ['label' => 'Requisitos']]"
/>

<section class="relative py-16 sm:py-20 bg-white">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        @foreach($groups as $group)
        <div class="mb-10">
            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 tracking-tight mb-5">{{ $group['title'] }}</h2>
            <ul class="space-y-3">
                @foreach($group['items'] as $label)
                <li class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-gray-50/60 px-5 py-4">
                    <div class="flex items-center justify-center w-7 h-7 rounded-full gradient-brand text-white shrink-0 mt-0.5">
                        <x-icon name="check" class="w-4 h-4" />
                    </div>
                    <span class="text-gray-800 leading-relaxed">{{ $label }}</span>
                </li>
                @endforeach
            </ul>
            @if(!empty($group['note']))
            <p class="mt-3 text-sm text-gray-500">{{ $group['note'] }}</p>
            @endif
        </div>
        @endforeach

        <div class="rounded-2xl border border-brand-100 bg-brand-50/60 px-6 py-5 mb-10">
            <p class="text-sm text-brand-900 leading-relaxed">
                <strong>Garantía:</strong> tu asesor te indicará si tu proceso será con aval o con pagarés, según el inmueble y las condiciones de la renta. En cuanto se defina, verás esa sección activa en tu portal.
            </p>
        </div>

        <div class="rounded-3xl gradient-brand px-8 py-10 text-center">
            <h3 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">¿Ya tienes todo listo?</h3>
            <p class="mt-2 text-brand-100">Escríbenos y te decimos los siguientes pasos.</p>
            <a href="{{ route('contacto') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                Contactar a un asesor
                <x-icon name="arrow-right" class="w-4 h-4" />
            </a>
        </div>

    </div>
</section>

@endsection
