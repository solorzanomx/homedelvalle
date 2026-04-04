@props(['src', 'alt' => '', 'class' => '', 'width' => null, 'height' => null, 'thumb' => null])

<div class="img-loader-wrap {{ $class }}" @if($width && $height) style="aspect-ratio: {{ $width }}/{{ $height }}" @endif>
    <div class="img-skeleton"></div>
    <img
        src="{{ $thumb ?: $src }}"
        data-src="{{ $src }}"
        alt="{{ $alt }}"
        loading="lazy"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        class="img-lazy"
        onload="this.classList.add('img-loaded'); this.previousElementSibling.style.display='none';"
    >
</div>
