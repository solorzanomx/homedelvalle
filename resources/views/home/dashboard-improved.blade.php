<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inmobiliaria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .toast { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .fade-out { animation: fadeOut 0.3s ease-in forwards; }
        @keyframes fadeOut { to { opacity: 0; transform: translateY(-10px); } }
        .pulse-ring { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 transition-colors">
    <!-- Navbar Mejorada -->
    <nav class="bg-white dark:bg-slate-800 shadow-sm border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        🏘 Inmobiliaria
                    </h1>
                </div>
                
                <div class="flex gap-4 items-center">
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition" title="Cambiar tema">
                        <span id="theme-icon">🌙</span>
                    </button>

                    @if (auth()->user()->role === 'admin')
                        <a href="{{ url('/admin') }}" class="px-4 py-2 rounded-lg bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 font-medium hover:bg-indigo-200 dark:hover:bg-indigo-800 transition">
                            ⚙️ Admin
                        </a>
                    @endif
                    
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900 text-red-600 dark:text-red-200 font-medium hover:bg-red-100 dark:hover:bg-red-800 transition">
                                🚪 Salir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-20 right-4 space-y-2 z-50"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <div class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            <span class="text-indigo-600 dark:text-indigo-400 font-medium">Dashboard</span>
        </div>

        <!-- Estadísticas con Sparklines Simulados -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 hover:shadow-md transition group cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">Propiedades</p>
                        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $properties->count() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-2">+12% vs mes anterior</p>
                    </div>
                    <div class="text-5xl opacity-20 group-hover:opacity-30 transition">🏠</div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 hover:shadow-md transition group cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">Clientes</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $clients->count() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-2">+8% vs mes anterior</p>
                    </div>
                    <div class="text-5xl opacity-20 group-hover:opacity-30 transition">👥</div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 hover:shadow-md transition group cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">Brokers</p>
                        <p class="text-3xl font-bold text-pink-600 dark:text-pink-400 mt-1">{{ $brokers->count() }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-2">+5% vs mes anterior</p>
                    </div>
                    <div class="text-5xl opacity-20 group-hover:opacity-30 transition">🤝</div>
                </div>
            </div>
        </div>

        <!-- Panel Principal con Tabs Mejorados -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Tab Navigation -->
            <div class="flex border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700">
                <button onclick="showTab('properties')" class="tab-btn active flex-1 px-6 py-4 text-center font-medium text-slate-700 dark:text-slate-300 border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-600 transition" data-tab="properties">
                    🏠 PROPIEDADES <span class="ml-2 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded">{{ $properties->count() }}</span>
                </button>
                <button onclick="showTab('clients')" class="tab-btn flex-1 px-6 py-4 text-center font-medium text-slate-700 dark:text-slate-300 hover:text-purple-600 dark:hover:text-purple-400 border-b-2 border-transparent hover:border-purple-300 dark:hover:border-purple-700 transition hover:bg-slate-100 dark:hover:bg-slate-600" data-tab="clients">
                    👥 CLIENTES <span class="ml-2 text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-1 rounded">{{ $clients->count() }}</span>
                </button>
                <button onclick="showTab('brokers')" class="tab-btn flex-1 px-6 py-4 text-center font-medium text-slate-700 dark:text-slate-300 hover:text-pink-600 dark:hover:text-pink-400 border-b-2 border-transparent hover:border-pink-300 dark:hover:border-pink-700 transition hover:bg-slate-100 dark:hover:bg-slate-600" data-tab="brokers">
                    🤝 BROKERS <span class="ml-2 text-xs bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 px-2 py-1 rounded">{{ $brokers->count() }}</span>
                </button>
            </div>

            <div class="p-6">
                <!-- TAB: PROPIEDADES -->
                <div id="properties-tab" class="tab-content">
                    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                        <div class="flex gap-2 flex-1 min-w-[250px]">
                            <input type="text" id="properties-search" onkeyup="filterTable('properties')" placeholder="🔍 Buscar propiedades..." class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none dark:bg-slate-700 dark:text-slate-200 transition">
                            <select id="properties-status-filter" onchange="filterTable('properties')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-slate-700 dark:text-slate-200 outline-none transition">
                                <option value="">Todos los estados</option>
                                <option value="available">Disponibles</option>
                                <option value="sold">Vendidas</option>
                            </select>
                        </div>
                        <a href="{{ route('properties.create') }}" class="px-4 py-2 bg-indigo-600 dark:bg-indigo-700 text-white font-medium rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition transform hover:scale-105">+ Nueva</a>
                    </div>
                    
                    @if ($properties->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Título</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Precio</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Estado</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Dirección</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-slate-600 dark:text-slate-300">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700" id="properties-table">
                                    @foreach ($properties as $property)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition property-row" data-title="{{ strtolower($property->title) }}" data-status="{{ $property->status }}">
                                            <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100 font-medium">{{ $property->title }}</td>
                                            <td class="px-6 py-4 text-sm text-indigo-600 dark:text-indigo-400 font-semibold">${{ number_format($property->price) }}</td>
                                            <td class="px-6 py-4 text-sm"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $property->status === 'available' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300' }}">{{ ucfirst($property->status) }}</span></td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ substr($property->address ?? 'N/A', 0, 30) }}...</td>
                                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                                <a href="{{ route('properties.edit', $property->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium transition">✏️</a>
                                                <button onclick="confirmDelete('{{ route('properties.destroy', $property->id) }}')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium transition">🗑️</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🏠</div>
                            <p class="text-lg text-slate-600 dark:text-slate-400 mb-2">No hay propiedades registradas</p>
                            <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Comienza creando tu primera propiedad</p>
                            <a href="{{ route('properties.create') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">Crear Primera Propiedad</a>
                        </div>
                    @endif
                </div>

                <!-- TAB: CLIENTES -->
                <div id="clients-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                        <div class="flex gap-2 flex-1 min-w-[250px]">
                            <input type="text" id="clients-search" onkeyup="filterTable('clients')" placeholder="🔍 Buscar clientes..." class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none dark:bg-slate-700 dark:text-slate-200 transition">
                            <select id="clients-type-filter" onchange="filterTable('clients')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:bg-slate-700 dark:text-slate-200 outline-none transition">
                                <option value="">Todos los tipos</option>
                                <option value="apartment">Apartamento</option>
                                <option value="house">Casa</option>
                                <option value="land">Terreno</option>
                            </select>
                        </div>
                        <a href="{{ route('clients.create') }}" class="px-4 py-2 bg-purple-600 dark:bg-purple-700 text-white font-medium rounded-lg hover:bg-purple-700 dark:hover:bg-purple-600 transition transform hover:scale-105">+ Nuevo</a>
                    </div>
                    
                    @if ($clients->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Nombre</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Email</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Teléfono</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Presupuesto</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-slate-600 dark:text-slate-300">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700" id="clients-table">
                                    @foreach ($clients as $client)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition client-row" data-name="{{ strtolower($client->name) }}" data-type="{{ $client->property_type }}">
                                            <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100 font-medium">{{ $client->name }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $client->email }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $client->phone ?? '—' }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $client->budget_min ? '$' . number_format($client->budget_min) : '—' }}</td>
                                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                                <a href="{{ route('clients.edit', $client->id) }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 font-medium transition">✏️</a>
                                                <button onclick="confirmDelete('{{ route('clients.destroy', $client->id) }}')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium transition">🗑️</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">👥</div>
                            <p class="text-lg text-slate-600 dark:text-slate-400 mb-2">No hay clientes registrados</p>
                            <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Comienza agregando tu primer cliente</p>
                            <a href="{{ route('clients.create') }}" class="inline-block px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition">Crear Primer Cliente</a>
                        </div>
                    @endif
                </div>

                <!-- TAB: BROKERS -->
                <div id="brokers-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                        <div class="flex gap-2 flex-1 min-w-[250px]">
                            <input type="text" id="brokers-search" onkeyup="filterTable('brokers')" placeholder="🔍 Buscar brokers..." class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent outline-none dark:bg-slate-700 dark:text-slate-200 transition">
                            <select id="brokers-status-filter" onchange="filterTable('brokers')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-pink-500 dark:bg-slate-700 dark:text-slate-200 outline-none transition">
                                <option value="">Todos</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Inactivos</option>
                            </select>
                        </div>
                        <a href="{{ route('brokers.create') }}" class="px-4 py-2 bg-pink-600 dark:bg-pink-700 text-white font-medium rounded-lg hover:bg-pink-700 dark:hover:bg-pink-600 transition transform hover:scale-105">+ Nuevo</a>
                    </div>
                    
                    @if ($brokers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Nombre</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Empresa</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Email</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Comisión</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600 dark:text-slate-300">Estado</th>
                                        <th class="px-6 py-3 text-right text-sm font-semibold text-slate-600 dark:text-slate-300">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700" id="brokers-table">
                                    @foreach ($brokers as $broker)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition broker-row" data-name="{{ strtolower($broker->name) }}" data-status="{{ $broker->status }}">
                                            <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100 font-medium">{{ $broker->name }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $broker->company_name ?? '—' }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $broker->email }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ $broker->commission_rate ?? '—' }}%</td>
                                            <td class="px-6 py-4 text-sm"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $broker->status === 'active' ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300' }}">{{ ucfirst($broker->status) }}</span></td>
                                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                                <a href="{{ route('brokers.edit', $broker->id) }}" class="text-pink-600 dark:text-pink-400 hover:text-pink-700 dark:hover:text-pink-300 font-medium transition">✏️</a>
                                                <button onclick="confirmDelete('{{ route('brokers.destroy', $broker->id) }}')" class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium transition">🗑️</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🤝</div>
                            <p class="text-lg text-slate-600 dark:text-slate-400 mb-2">No hay brokers registrados</p>
                            <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Comienza agregando tu primer broker</p>
                            <a href="{{ route('brokers.create') }}" class="inline-block px-6 py-3 bg-pink-600 text-white font-medium rounded-lg hover:bg-pink-700 transition">Crear Primer Broker</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminar -->
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">¿Estás seguro?</h3>
            <p class="text-slate-600 dark:text-slate-400 mb-6">Esta acción no se puede deshacer.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-medium rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                    Cancelar
                </button>
                <form id="delete-form" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Toggle
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                document.getElementById('theme-icon').textContent = '🌙';
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                document.getElementById('theme-icon').textContent = '☀️';
            }
        }

        // Restaurar tema guardado
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.getElementById('theme-icon').textContent = '☀️';
            }
        });

        // Tab Switching
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active', 'border-indigo-600', 'border-purple-600', 'border-pink-600', 'text-indigo-600', 'text-indigo-400', 'text-purple-600', 'text-purple-400', 'text-pink-600', 'text-pink-400');
                b.classList.add('border-transparent', 'text-slate-700');
            });
            
            const btn = document.querySelector(`[data-tab="${tabName}"]`);
            btn.classList.add('active');
            btn.classList.remove('border-transparent', 'text-slate-700');
            
            if (tabName === 'properties') {
                btn.classList.add('border-indigo-600', 'text-indigo-600');
            } else if (tabName === 'clients') {
                btn.classList.add('border-purple-600', 'text-purple-600');
            } else if (tabName === 'brokers') {
                btn.classList.add('border-pink-600', 'text-pink-600');
            }
        }

        // Filtrado de Tablas
        function filterTable(type) {
            const searchInput = document.getElementById(type + '-search');
            const filterSelect = document.getElementById(type + '-' + (type === 'properties' ? 'status' : type === 'clients' ? 'type' : 'status') + '-filter');
            const table = document.getElementById(type + '-table');
            const rows = table.querySelectorAll('.' + type.slice(0, -1) + '-row');
            
            const searchTerm = searchInput.value.toLowerCase();
            const filterValue = filterSelect ? filterSelect.value : '';
            
            rows.forEach(row => {
                let showRow = true;
                
                // Filtrado por búsqueda
                const cells = row.textContent.toLowerCase();
                if (searchTerm && !cells.includes(searchTerm)) {
                    showRow = false;
                }
                
                // Filtrado por estado/tipo
                if (filterValue) {
                    const dataAttr = type === 'clients' ? 'data-type' : 'data-status';
                    if (row.getAttribute(dataAttr) !== filterValue) {
                        showRow = false;
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }

        // Confirmación de Eliminar
        function confirmDelete(actionUrl) {
            const deleteForm = document.getElementById('delete-form');
            deleteForm.action = actionUrl;
            document.getElementById('delete-modal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('delete-modal').classList.add('hidden');
        }

        // Cerrar modal al presionar ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });

        // Toast Notifications (para mensajes de éxito después de acciones)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
            
            const toast = document.createElement('div');
            toast.className = `toast ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3`;
            toast.innerHTML = `<span class="text-xl">${icon}</span><span>${message}</span>`;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Mostrar notificación si viene de sesión anterior
        @if (session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
    </script>
</body>
</html>
