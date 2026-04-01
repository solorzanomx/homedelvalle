<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="mb-8">
                <a href="/" class="text-purple-600 hover:text-purple-700 font-medium text-sm flex items-center gap-2">
                    ← Volver al Dashboard
                </a>
                <h1 class="text-4xl font-bold text-slate-900 dark:text-slate-100 mt-4">👥 Editar Cliente</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-2">{{ $client->name }}</p>
            </div>

            <!-- Formulario -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Errores en el formulario:</h3>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-red-700 dark:text-red-300 text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('clients.update', $client->id) }}" method="POST" class="space-y-6">
                    @csrf @method('PUT')
                    
                    <!-- Sección 1: Información Personal -->
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Información Personal</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nombre *</label>
                                <input type="text" name="name" value="{{ $client->name }}" required class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email *</label>
                                <input type="email" name="email" value="{{ $client->email }}" required class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Teléfono</label>
                                <input type="tel" name="phone" value="{{ $client->phone }}" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Ciudad</label>
                                <input type="text" name="city" value="{{ $client->city }}" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200">
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Preferencias -->
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Preferencias de Compra</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tipo de Propiedad</label>
                                <select name="property_type" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200">
                                    <option value="">Seleccionar...</option>
                                    <option value="apartment" {{ $client->property_type === 'apartment' ? 'selected' : '' }}>🏢 Apartamento</option>
                                    <option value="house" {{ $client->property_type === 'house' ? 'selected' : '' }}>🏠 Casa</option>
                                    <option value="condo" {{ $client->property_type === 'condo' ? 'selected' : '' }}>🏘️ Condominio</option>
                                    <option value="land" {{ $client->property_type === 'land' ? 'selected' : '' }}>📍 Terreno</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Rango de Presupuesto</label>
                                <div class="flex gap-2">
                                    <input type="number" name="budget_min" value="{{ $client->budget_min }}" placeholder="Min" class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg outline-none dark:bg-slate-700 dark:text-slate-200">
                                    <span class="flex items-center text-slate-600 dark:text-slate-400">—</span>
                                    <input type="number" name="budget_max" value="{{ $client->budget_max }}" placeholder="Max" class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg outline-none dark:bg-slate-700 dark:text-slate-200">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Dirección -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Dirección de Preferencia</h3>
                        <textarea name="address" rows="4" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200">{{ $client->address }}</textarea>
                    </div>

                    <!-- Metadata -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                        <p>✓ Creado: {{ $client->created_at->format('d/m/Y H:i') }}</p>
                        <p>↻ Actualizado: {{ $client->updated_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-pink-700 transition transform hover:scale-105 active:scale-95 shadow-lg">
                            ✓ Guardar Cambios
                        </button>
                        <a href="/" class="flex-1 px-6 py-3 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition text-center">
                            ✕ Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
