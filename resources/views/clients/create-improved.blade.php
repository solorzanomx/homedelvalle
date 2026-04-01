<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="mb-8">
                <a href="/" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm flex items-center gap-2">
                    ← Volver al Dashboard
                </a>
                <h1 class="text-4xl font-bold text-slate-900 dark:text-slate-100 mt-4">👥 Crear Nuevo Cliente</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-2">Completa el formulario para agregar un nuevo cliente</p>
            </div>

            <!-- Formulario Mejorado -->
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

                <form action="{{ route('clients.store') }}" method="POST" id="clientForm" class="space-y-6">
                    @csrf
                    
                    <!-- Sección 1: Información Personal -->
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Información Personal</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div class="form-group">
                                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Nombre Completo *
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name') }}" 
                                    required 
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none dark:bg-slate-700 dark:text-slate-200 transition" 
                                    placeholder="Juan Pérez"
                                    onchange="validateField(this)">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Mínimo 3 caracteres</p>
                                <span class="error-message text-red-600 dark:text-red-400 text-sm hidden"></span>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Email *
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}" 
                                    required 
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none dark:bg-slate-700 dark:text-slate-200 transition" 
                                    placeholder="juan@example.com"
                                    onchange="validateField(this)">
                                <span class="error-message text-red-600 dark:text-red-400 text-sm hidden"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Teléfono -->
                            <div class="form-group">
                                <label for="phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Teléfono
                                </label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    value="{{ old('phone') }}" 
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200 transition" 
                                    placeholder="+34 600 000 000">
                            </div>

                            <!-- Ciudad -->
                            <div class="form-group">
                                <label for="city" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Ciudad
                                </label>
                                <input 
                                    type="text" 
                                    id="city" 
                                    name="city" 
                                    value="{{ old('city') }}" 
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200 transition" 
                                    placeholder="Madrid">
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Preferencias de Compra -->
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-6">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Preferencias de Compra</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tipo de Propiedad -->
                            <div class="form-group">
                                <label for="property_type" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Tipo de Propiedad
                                </label>
                                <select 
                                    id="property_type" 
                                    name="property_type" 
                                    class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200 transition">
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="apartment" {{ old('property_type') === 'apartment' ? 'selected' : '' }}>🏢 Apartamento</option>
                                    <option value="house" {{ old('property_type') === 'house' ? 'selected' : '' }}>🏠 Casa</option>
                                    <option value="condo" {{ old('property_type') === 'condo' ? 'selected' : '' }}>🏘️ Condominio</option>
                                    <option value="land" {{ old('property_type') === 'land' ? 'selected' : '' }}>📍 Terreno</option>
                                </select>
                            </div>

                            <!-- Presupuesto Rango -->
                            <div class="form-group">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Rango de Presupuesto
                                </label>
                                <div class="flex gap-2">
                                    <input 
                                        type="number" 
                                        name="budget_min" 
                                        value="{{ old('budget_min') }}" 
                                        class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200" 
                                        placeholder="Min">
                                    <span class="flex items-center text-slate-600 dark:text-slate-400">—</span>
                                    <input 
                                        type="number" 
                                        name="budget_max" 
                                        value="{{ old('budget_max') }}" 
                                        class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200" 
                                        placeholder="Max">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Dirección -->
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Dirección de Preferencia</h3>
                        
                        <textarea 
                            id="address" 
                            name="address" 
                            rows="4" 
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none dark:bg-slate-700 dark:text-slate-200 transition" 
                            placeholder="Ingresa la dirección o zona de preferencia...">{{ old('address') }}</textarea>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Formulario completado</span>
                            <span id="progress-percent" class="text-xs font-semibold text-purple-600 dark:text-purple-400">0%</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                            <div id="progress-bar" class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex gap-4 pt-6">
                        <button 
                            type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-pink-700 transition transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                            id="submit-btn">
                            ✓ Crear Cliente
                        </button>
                        <a 
                            href="/" 
                            class="flex-1 px-6 py-3 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 font-semibold rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition text-center">
                            ✕ Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tips Útiles -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">💡 Tip</p>
                    <p class="text-xs text-blue-800 dark:text-blue-300 mt-1">Los campos marcados con * son obligatorios</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <p class="text-sm font-semibold text-green-900 dark:text-green-200">✓ Validación</p>
                    <p class="text-xs text-green-800 dark:text-green-300 mt-1">El formulario se valida en tiempo real</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">⌨️ Atajo</p>
                    <p class="text-xs text-amber-800 dark:text-amber-300 mt-1">Presiona Ctrl+Enter para enviar (Cmd en Mac)</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación en Tiempo Real
        function validateField(field) {
            const errorSpan = field.parentElement.querySelector('.error-message');
            let isValid = true;
            let errorMsg = '';

            if (field.id === 'name') {
                if (field.value.length < 3) {
                    isValid = false;
                    errorMsg = 'El nombre debe tener al menos 3 caracteres';
                }
            } else if (field.id === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                    errorMsg = 'Ingresa un email válido';
                }
            }

            if (!isValid) {
                field.classList.add('border-red-500', 'focus:ring-red-500');
                field.classList.remove('border-slate-300', 'focus:ring-purple-500');
                if (errorSpan) {
                    errorSpan.textContent = errorMsg;
                    errorSpan.classList.remove('hidden');
                }
            } else {
                field.classList.remove('border-red-500', 'focus:ring-red-500');
                field.classList.add('border-slate-300', 'focus:ring-purple-500');
                if (errorSpan) {
                    errorSpan.classList.add('hidden');
                }
            }

            updateProgress();
        }

        // Calcular Progreso del Formulario
        function updateProgress() {
            const form = document.getElementById('clientForm');
            const inputs = form.querySelectorAll('input, textarea, select');
            let filled = 0;

            inputs.forEach(input => {
                if (input.value.trim() !== '') {
                    filled++;
                }
            });

            const percentage = Math.round((filled / inputs.length) * 100);
            const progressBar = document.getElementById('progress-bar');
            const progressPercent = document.getElementById('progress-percent');

            progressBar.style.width = percentage + '%';
            progressPercent.textContent = percentage + '%';
        }

        // Validar cambios de campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').addEventListener('change', function() { validateField(this); });
            document.getElementById('email').addEventListener('change', function() { validateField(this); });
            
            // Validar en tiempo real
            document.querySelectorAll('input, textarea, select').forEach(el => {
                el.addEventListener('input', updateProgress);
            });

            // Atajo de Teclado: Ctrl+Enter para enviar
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    document.getElementById('submit-btn').click();
                }
            });
        });
    </script>
</body>
</html>
