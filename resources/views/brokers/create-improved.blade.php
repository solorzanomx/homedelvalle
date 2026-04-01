<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Broker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-2xl">
            <div class="mb-8">
                <a href="/" class="text-pink-600 hover:text-pink-700 font-medium text-sm flex items-center gap-2">← Volver al Dashboard</a>
                <h1 class="text-4xl font-bold text-slate-900 dark:text-slate-100 mt-4">🤝 Crear Nuevo Broker</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-2">Completa el formulario para agregar un nuevo broker</p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Errores:</h3>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-red-700 dark:text-red-300 text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('brokers.store') }}" method="POST" id="brokerForm" class="space-y-6">
                    @csrf
                    
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Información Personal</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nombre *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="Carlos López">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="carlos@example.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Teléfono</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="+34 600 000 000">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Empresa</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="Inmobiliaria XYZ">
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Documentación</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Número de Licencia</label>
                                <input type="text" name="license_number" value="{{ old('license_number') }}" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="LIC-12345">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Comisión (%)</label>
                                <input type="number" name="commission_rate" value="{{ old('commission_rate') }}" step="0.01" min="0" max="100" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="5.5">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Estado</label>
                            <select name="status" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>✓ Activo</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>✕ Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Bio / Descripción</h3>
                        <textarea name="bio" rows="4" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 outline-none dark:bg-slate-700 dark:text-slate-200" placeholder="Información sobre el broker...">{{ old('bio') }}</textarea>
                    </div>

                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-pink-600 to-rose-600 text-white font-semibold rounded-lg hover:from-pink-700 hover:to-rose-700 transition transform hover:scale-105 active:scale-95 shadow-lg">
                            ✓ Crear Broker
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
