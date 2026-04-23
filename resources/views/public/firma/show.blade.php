@extends('layouts.public')
@section('title', 'Estado de Firma — ' . ($request->document_name ?? 'Documento'))

@section('content')
<section class="py-20 min-h-[60vh] flex items-center">
    <div class="container mx-auto px-4 max-w-lg text-center">

        @if($request->isCompleted())

        <div class="bg-white rounded-2xl shadow-lg p-10">
            <div class="text-5xl mb-4">&#10003;</div>
            <h1 class="text-xl font-bold text-gray-900 mb-3">¡Contrato firmado!</h1>
            <p class="text-gray-600 mb-2">Hola <strong>{{ $request->contacto->name ?? 'Cliente' }}</strong>,</p>
            <p class="text-gray-600 mb-6">
                Hemos recibido tu firma del contrato de confidencialidad. Tu acceso al portal de
                evaluación de tu propiedad ya está activo.
            </p>
            @if($request->contacto?->user_id)
            <a href="{{ route('portal.dashboard') }}"
               class="inline-block rounded-xl bg-brand-500 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-600 transition">
                Ir a mi portal
            </a>
            @else
            <p class="text-sm text-gray-500">Recibirás tus credenciales de acceso por correo en breve.</p>
            @endif
        </div>

        @else

        <div class="bg-white rounded-2xl shadow-lg p-10">
            <div class="text-5xl mb-4">&#9993;</div>
            <h1 class="text-xl font-bold text-gray-900 mb-3">Firma pendiente</h1>
            <p class="text-gray-600 mb-2">Hola <strong>{{ $request->contacto->name ?? 'Cliente' }}</strong>,</p>
            <p class="text-gray-600 mb-6">
                Te hemos enviado un correo a <strong>{{ $request->contacto->email ?? '' }}</strong> con
                el enlace para firmar tu contrato de confidencialidad.<br><br>
                Una vez que firmes, tu acceso al portal de valuación se activará automáticamente.
            </p>
            <p class="text-sm text-gray-400">
                ¿No recibiste el correo? Contacta a tu agente para que lo reenvíe.
            </p>
        </div>

        @endif

    </div>
</section>
@endsection
