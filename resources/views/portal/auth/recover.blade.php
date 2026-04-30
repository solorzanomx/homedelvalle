@extends('layouts.portal-empty')
@section('title', 'Recuperar contraseña')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-1">Recuperar contraseña</h1>
<p class="text-sm text-gray-500 mb-8">Ingresa tu correo y te enviamos un enlace para restablecer tu contraseña.</p>

<form method="POST" action="{{ route('portal.recover.submit') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
               class="w-full rounded-xl border @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
        @error('email')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
            class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm py-3.5 transition-all shadow-sm">
        Enviar enlace de recuperación
    </button>
</form>

<p class="mt-6 text-center text-xs text-gray-400">
    <a href="{{ route('portal.login') }}" class="text-brand-600 hover:underline">← Volver al inicio de sesión</a>
</p>
@endsection
