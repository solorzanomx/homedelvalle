@props(['name', 'class' => 'w-5 h-5'])
@php
    $path = str_contains($name, '/')
        ? resource_path("svg/{$name}.svg")
        : resource_path("svg/lucide/{$name}.svg");

    $svg = cache()->remember("icon.{$name}:" . filemtime($path), 86400, function() use ($path) {
        return file_exists($path) ? file_get_contents($path) : '';
    });

    // Inject class, remove fixed width/height
    $svg = preg_replace('/\s(width|height)="[^"]*"/', '', $svg);
    $svg = preg_replace('/<svg\b/', '<svg class="' . e($class) . '"', $svg, 1);
@endphp
{!! $svg !!}
