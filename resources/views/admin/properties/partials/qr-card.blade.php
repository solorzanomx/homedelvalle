<div class="side-card">
    <div class="side-card-header">Código QR</div>
    <div class="side-card-body">
        @if($property->qrCode)
            <!-- QR Existe -->
            <div style="text-align: center; margin-bottom: 1rem;">
                <img
                    src="{{ Storage::url($property->qrCode->qr_code_path) }}"
                    alt="QR Code"
                    style="width: 180px; height: 180px; object-fit: contain;"
                >
            </div>

            <div style="border-top: 1px solid var(--border); padding-top: 0.75rem; margin-bottom: 0.75rem; font-size: 0.78rem;">
                <div style="margin-bottom: 0.5rem;">
                    <strong style="display: block; margin-bottom: 0.25rem;">URL:</strong>
                    <code style="display: block; background: var(--bg); padding: 0.5rem; border-radius: 4px; word-break: break-all; font-size: 0.7rem; color: var(--text-muted);">
                        {{ $property->qrCode->qr_url }}
                    </code>
                </div>
                <div style="color: var(--text-muted);">
                    <strong>Generado:</strong> {{ $property->qrCode->generated_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                <form action="{{ route('properties.qr.generate', $property) }}" method="POST" style="flex: 1;">
                    @csrf
                    <input type="hidden" name="force" value="1">
                    <button
                        type="submit"
                        class="btn btn-sm btn-outline"
                        style="width: 100%; justify-content: center; font-size: 0.75rem;"
                    >
                        🔄 Regenerar
                    </button>
                </form>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                <a
                    href="{{ route('properties.qr.download', ['property' => $property, 'format' => 'png']) }}"
                    class="btn btn-sm btn-outline"
                    style="flex: 1; justify-content: center; font-size: 0.75rem; text-decoration: none;"
                    download
                >
                    📥 PNG
                </a>

                <a
                    href="{{ route('properties.qr.download', ['property' => $property, 'format' => 'svg']) }}"
                    class="btn btn-sm btn-outline"
                    style="flex: 1; justify-content: center; font-size: 0.75rem; text-decoration: none;"
                    download
                >
                    📥 SVG
                </a>
            </div>

            <form
                action="{{ route('properties.qr.delete', $property) }}"
                method="POST"
                onsubmit="return confirm('¿Eliminar este QR?');"
                style="margin-top: 0.5rem;"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="btn btn-sm btn-danger"
                    style="width: 100%; justify-content: center; font-size: 0.75rem;"
                >
                    🗑️ Eliminar
                </button>
            </form>
        @else
            <!-- QR No existe -->
            <div style="text-align: center; padding: 1rem 0;">
                <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.85rem;">
                    No hay QR generado
                </p>
                <form action="{{ route('properties.qr.generate', $property) }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="btn btn-sm"
                        style="width: 100%; justify-content: center;"
                    >
                        ✨ Generar QR
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

