@props(['size' => '32', 'label', 'bg', 'fg'])

<div style="display: inline-block; width: {{ $size }}px; height: {{ $size }}px; border-radius: 50%; background-color: {{ $bg }}; color: {{ $fg }}; font-size: {{ $size / 2 }}px; font-weight: 600; line-height: {{ $size }}px; text-align: center;">
    {{ $label }}
</div>
