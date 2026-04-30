@extends('layouts.portal-empty')
@section('title', 'Accede a tu portal')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-1">Bienvenido</h1>
<p class="text-sm text-gray-500 mb-8">Accede a tu portal para ver el estado de tu operación.</p>

<form method="POST" action="{{ route('portal.login.submit') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
               class="w-full rounded-xl border @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
        @error('email')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
            <a href="{{ route('portal.recover') }}" class="text-xs text-brand-600 hover:text-brand-700 transition-colors">¿Olvidaste tu contraseña?</a>
        </div>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
    </div>

    <div class="flex items-center gap-2.5">
        <input id="remember" type="checkbox" name="remember" class="rounded text-brand-500 border-gray-300">
        <label for="remember" class="text-sm text-gray-600 select-none">Mantener sesión iniciada</label>
    </div>

    <button type="submit"
            class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-semibold text-sm py-3.5 transition-all shadow-sm">
        Acceder a mi portal
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
    </button>
</form>

<p class="mt-6 text-center text-xs text-gray-400">
    ¿Eres nuevo? Revisa tu correo electrónico — te enviamos un enlace de activación.
</p>
@endsection
