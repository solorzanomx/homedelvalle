@extends('layouts.portal-empty')
@section('title', 'Nueva contraseña')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-1">Crear nueva contraseña</h1>
<p class="text-sm text-gray-500 mb-8">Elige una contraseña segura para tu cuenta.</p>

<form method="POST" action="{{ route('portal.reset.submit') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required autofocus
               class="w-full rounded-xl border @error('email') border-red-300 bg-red-50 @else border-gray-200 @enderror px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
        @error('email')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
               class="w-full rounded-xl border @error('password') border-red-300 bg-red-50 @else border-gray-200 @enderror px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
        <p class="mt-1 text-xs text-gray-400">Mínimo 8 caracteres.</p>
        @error('password')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 outline-none transition">
    </div>

    <button type="submit"
            class="w-full flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm py-3.5 transition-all shadow-sm">
        Guardar nueva contraseña
    </button>
</form>
@endsection
