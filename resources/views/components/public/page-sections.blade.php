{{-- Page Sections Renderer --}}
@props(['sections' => []])

@foreach($sections as $section)
    @switch($section['type'] ?? '')
        @case('hero')
            @include('components.public.sections.hero', ['data' => $section['data'] ?? []])
            @break
        @case('content')
            @include('components.public.sections.content', ['data' => $section['data'] ?? []])
            @break
        @case('cta')
            @include('components.public.sections.cta', ['data' => $section['data'] ?? []])
            @break
        @case('gallery')
            @include('components.public.sections.gallery', ['data' => $section['data'] ?? []])
            @break
        @case('cards')
            @include('components.public.sections.cards', ['data' => $section['data'] ?? []])
            @break
        @case('testimonials')
            @include('components.public.sections.testimonials', ['data' => $section['data'] ?? []])
            @break
        @case('contact_form')
            @include('components.public.sections.contact-form', ['data' => $section['data'] ?? []])
            @break
        @case('html')
            @include('components.public.sections.html', ['data' => $section['data'] ?? []])
            @break
    @endswitch
@endforeach
