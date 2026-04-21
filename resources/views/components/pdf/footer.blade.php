{{-- Componente: Footer / Contacto --}}
{{-- Uso: @include('components.pdf.footer', ['property' => $property, 'showQr' => true]) --}}

<div class="footer-section">
    <div class="footer-contact">
        <div class="footer-contact-title">HOME DEL VALLE</div>
        <div class="footer-contact-item">
            <strong>Inmobiliaria Premium</strong>
        </div>
        <div class="footer-contact-item">
            📱 +52 55 1234 5678
        </div>
        <div class="footer-contact-item">
            ✉️ info@homedelvalle.mx
        </div>
    </div>

    @if(isset($showQr) && $showQr && $property->qr_path)
        <div class="footer-qr">
            <img src="{{ public_path('storage/' . $property->qr_path) }}" alt="QR Propiedad" class="qr-image">
            <div class="qr-label">Escanea para<br>más información</div>
        </div>
    @endif
</div>
