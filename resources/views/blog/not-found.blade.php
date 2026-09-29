@extends('layouts.public')

@section('meta')
    <x-public.seo-meta title="Artículo no encontrado" description="Este artículo ya no existe, pero tenemos otros que te pueden servir." />
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <div class="text-5xl mb-4">🔎</div>
    <h1 class="text-2xl font-extrabold text-gray-900">No encontramos ese artículo</h1>
    <p class="mt-2 text-gray-500">Puede que haya cambiado de dirección o ya no esté disponible.</p>

    @if($related->isNotEmpty())
    <div class="mt-10 text-left">
        <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-3">Artículos que te pueden interesar</div>
        <div class="grid gap-3">
            @foreach($related as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="block rounded-xl border border-gray-200 p-4 hover:border-brand-300 hover:bg-brand-50/40 transition-colors">
                <div class="font-bold text-gray-900">{{ $post->title }}</div>
                @if($post->excerpt)<div class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($post->excerpt, 110) }}</div>@endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <a href="{{ route('contacto') }}" class="inline-flex items-center gap-2 mt-10 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-brand" style="background: var(--color-primary, #3B82C4);">
        ¿No encuentras lo que buscabas? Escríbenos
    </a>
</div>
@endsection
