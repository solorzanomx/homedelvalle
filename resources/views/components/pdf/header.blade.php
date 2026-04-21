{{-- Componente: Header Corporativo --}}
{{-- Uso: @include('components.pdf.header', ['showBorder' => true]) --}}

<div class="header">
    <div class="header-top">
        <div class="logo">
            @if(file_exists(public_path('images/logo-homedelvalle.png')))
                <img src="{{ public_path('images/logo-homedelvalle.png') }}" alt="Home del Valle">
            @else
                <div style="font-weight: 700; color: var(--primary-dark); font-size: 18px;">
                    HOME<br>DEL VALLE
                </div>
            @endif
        </div>
        <div class="header-info">
            <div><strong>HOME DEL VALLE</strong></div>
            <div>Inmobiliaria Premium</div>
            <div>📱 +52 55 1234 5678</div>
            <div>✉️ info@homedelvalle.mx</div>
        </div>
    </div>
</div>
