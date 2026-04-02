@props(['type', 'data' => []])

@php
    $jsonLd = array_merge(["\x40context" => 'https://schema.org', "\x40type" => $type], $data);
@endphp
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
