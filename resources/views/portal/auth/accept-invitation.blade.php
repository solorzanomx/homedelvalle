@extends('layouts.portal-empty')
@section('title', 'Activa tu cuenta')

@section('content')
@if(!$valid)
    {{-- Token inválido o expirado --}}
    <div class="text-center">
        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-50 mx-auto mb-5">
            <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Enlace inválido o expirado</h1>
        <p class="text-sm text-gray-500 mb-6">Este enlace de activación ya no es válido. Puede haber expirado (duran 7 días) o ya fue usado.</p>
        <p class="text-sm text-gray-500">Pide a tu asesor que te reenvíe el correo de bienvenida o escríbenos a
            <a href="mailto:contacto@homedelvalle.mx" class="text-brand-600 hover:underline">contacto@homedelvalle.mx</a>.
        </p>
    </div>
@else
    {{-- Activación de cuenta --}}
    <div class="text-center mb-7">
        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-brand-50 mx-auto mb-4">
            <svg class="w-7 h-7 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Activa tu cuenta</h1>
        <p class="text-sm text-gray-500">Hola <strong>{{ $name }}</strong>, crea tu contraseña para entrar a tu portal.</p>
    </div>

    <form method="POST" action="{{ route('portal.accept-invitation.submit') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
            <input type="email" value="{{ $email }}" disabled
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Crea tu contraseña</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                   class="w-full rounded-xl border @error('password') border-red-300 bg-red-50 @else border-gray-200 @enderror px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
            <p class="mt-1 text-xs text-gray-400">Mínimo 8 caracteres.</p>
            @error('password')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirma tu contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm py-3.5 transition-all shadow-sm">
            Activar mi cuenta y entrar
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>
    </form>
@endif
@endsection
