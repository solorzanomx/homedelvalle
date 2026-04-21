<div class="bg-white rounded-lg border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Código QR</h3>

    @if($property->qrCode)
        <!-- QR Existe -->
        <div class="space-y-4">
            <!-- Vista previa del QR -->
            <div class="flex justify-center p-4 bg-gray-50 rounded-lg">
                <img
                    src="{{ Storage::url($property->qrCode->qr_code_path) }}"
                    alt="QR Code"
                    class="w-48 h-48 object-contain"
                >
            </div>

            <!-- Información del QR -->
            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-2">
                    <strong>URL codificada:</strong><br>
                    <code class="text-xs bg-gray-100 p-2 rounded block break-all">
                        {{ $property->qrCode->qr_url }}
                    </code>
                </p>
                <p class="text-sm text-gray-500">
                    <strong>Generado:</strong> {{ $property->qrCode->generated_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-2 pt-4 border-t">
                <form action="{{ route('properties.qr.generate', $property) }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="force" value="1">
                    <button
                        type="submit"
                        class="w-full px-3 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition"
                    >
                        Regenerar QR
                    </button>
                </form>

                <a
                    href="{{ route('properties.qr.download', ['property' => $property, 'format' => 'png']) }}"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                    download
                >
                    Descargar PNG
                </a>

                <a
                    href="{{ route('properties.qr.download', ['property' => $property, 'format' => 'svg']) }}"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                    download
                >
                    Descargar SVG
                </a>
            </div>

            <!-- Eliminar QR -->
            <form
                action="{{ route('properties.qr.delete', $property) }}"
                method="POST"
                onsubmit="return confirm('¿Eliminar este QR?')"
                class="pt-2 border-t"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition"
                >
                    Eliminar QR
                </button>
            </form>
        </div>
    @else
        <!-- QR No existe -->
        <div class="text-center py-8">
            <p class="text-gray-600 mb-4">No hay código QR generado para esta propiedad.</p>
            <form action="{{ route('properties.qr.generate', $property) }}" method="POST">
                @csrf
                <button
                    type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition"
                >
                    Generar QR
                </button>
            </form>
        </div>
    @endif
</div>
