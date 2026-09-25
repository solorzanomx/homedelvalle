{{-- Guía general "cómo subir bien tus documentos" (todas las categorías). Fuente: DocumentUploadGuide --}}
@php
    $kinds = [
        'statement' => ['📄', 'Estados de cuenta, nóminas y comprobantes de ingresos'],
        'utility' => ['🧾', 'Recibos de luz, agua, gas y comprobante de domicilio'],
        'id' => ['🪪', 'Identificaciones'],
        'legal' => ['📑', 'Escrituras, predial, actas y documentos legales'],
        'payment' => ['💳', 'Comprobantes de pago'],
    ];
    $sampleCat = ['statement' => 'estado_cuenta', 'utility' => 'luz', 'id' => 'ine_frente', 'legal' => 'escritura', 'payment' => 'comprobante_apartado'];
@endphp
<details style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:.85rem 1rem;margin-bottom:1rem;">
    <summary style="cursor:pointer;font-size:.88rem;font-weight:700;color:#1e3a8a;list-style:none;">
        📎 Cómo subir tus documentos para que se aprueben a la primera
        <span style="display:block;font-size:.75rem;font-weight:500;color:#475569;margin-top:.15rem;">Regla de oro: el PDF original siempre se lee mejor que una foto. <u>Nunca le tomes foto a una pantalla.</u></span>
    </summary>
    <div style="margin-top:.75rem;display:grid;gap:.6rem;">
        @foreach($kinds as $k => [$icon, $title])
            @php $g = \App\Support\DocumentUploadGuide::for($sampleCat[$k]); @endphp
            <div style="background:#fff;border:1px solid #dbeafe;border-radius:9px;padding:.6rem .8rem;">
                <div style="font-size:.8rem;font-weight:700;color:#0f172a;">{{ $icon }} {{ $title }}</div>
                <div style="font-size:.74rem;color:#475569;margin:.15rem 0 .3rem;">{{ $g['headline'] }}</div>
                <ul style="margin:0;padding:0;list-style:none;font-size:.72rem;line-height:1.45;">
                    @foreach($g['do'] as $t)<li style="color:#166534;">✅ {{ $t }}</li>@endforeach
                    @foreach($g['dont'] as $t)<li style="color:#991b1b;">❌ {{ $t }}</li>@endforeach
                </ul>
            </div>
        @endforeach
    </div>
</details>
