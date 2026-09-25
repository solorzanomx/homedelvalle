{{-- Leyenda de cómo subir un documento. Espera: $category. Opcional: $open (bool). Fuente: App\Support\DocumentUploadGuide --}}
@php
    $g = \App\Support\DocumentUploadGuide::for($category ?? null);
    $open = $open ?? in_array($g['kind'], ['statement', 'utility'], true);
@endphp
<details class="up-tips" @if($open) open @endif style="margin:.5rem 0 .25rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:.5rem .75rem;">
    <summary style="cursor:pointer;font-size:.75rem;font-weight:700;color:#1D4ED8;list-style:none;">
        💡 {{ $g['headline'] }} <span style="font-weight:500;color:#94a3b8;">· ver consejos</span>
    </summary>
    <ul style="margin:.5rem 0 .25rem;padding:0;list-style:none;font-size:.74rem;line-height:1.45;">
        @foreach($g['do'] as $t)<li style="color:#166534;margin-bottom:.2rem;">✅ {{ $t }}</li>@endforeach
        @foreach($g['dont'] as $t)<li style="color:#991b1b;margin-bottom:.2rem;">❌ {{ $t }}</li>@endforeach
    </ul>
    @include('portal._upload_examples')
    <div style="font-size:.68rem;color:#94a3b8;">Formatos: PDF, JPG o PNG · máximo 10 MB</div>
</details>
