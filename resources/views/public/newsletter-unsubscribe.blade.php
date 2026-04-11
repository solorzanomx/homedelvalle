@extends('layouts.public')
@section('title', 'Newsletter')

@section('content')
<section class="py-20 min-h-[60vh] flex items-center">
    <div class="container mx-auto px-4 max-w-lg text-center">
        @if($status === 'success')
        <div class="bg-white rounded-2xl shadow-lg p-10">
            <div class="text-4xl mb-4">&#9993;</div>
            <h1 class="text-xl font-bold text-gray-900 mb-3">Suscripcion cancelada</h1>
            <p class="text-gray-600 mb-6">Has cancelado tu suscripcion al newsletter exitosamente. Ya no recibiras correos nuestros.</p>
            <a href="{{ url('/') }}" class="inline-block rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-600 transition">Volver al inicio</a>
        </div>
        @elseif($status === 'already')
        <div class="bg-white rounded-2xl shadow-lg p-10">
            <div class="text-4xl mb-4">&#10003;</div>
            <h1 class="text-xl font-bold text-gray-900 mb-3">Ya cancelaste tu suscripcion</h1>
            <p class="text-gray-600 mb-6">Tu email ya no esta suscrito al newsletter.</p>
            <a href="{{ url('/') }}" class="inline-block rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-600 transition">Volver al inicio</a>
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-lg p-10">
            <div class="text-4xl mb-4">&#10060;</div>
            <h1 class="text-xl font-bold text-gray-900 mb-3">Link invalido</h1>
            <p class="text-gray-600 mb-6">Este enlace de desuscripcion no es valido o ha expirado.</p>
            <a href="{{ url('/') }}" class="inline-block rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-600 transition">Volver al inicio</a>
        </div>
        @endif
    </div>
</section>
@endsection
