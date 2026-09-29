@extends('layouts.public')

@section('meta')
    <x-public.seo-meta title="Artículo retirado" description="Este artículo se retiró del blog." />
@endsection

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">
    <div class="text-5xl mb-4">🗂️</div>
    <h1 class="text-2xl font-extrabold text-gray-900">Este artículo ya no está disponible</h1>
    <p class="mt-2 text-gray-500">Lo retiramos del blog a propósito.</p>
    <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 mt-8 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-brand" style="background: var(--color-primary, #3B82C4);">
        Ver todos los artículos
    </a>
</div>
@endsection
