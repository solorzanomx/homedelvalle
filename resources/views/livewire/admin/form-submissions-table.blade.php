<div wire:poll.10s>

    {{-- Toast flotante --}}
    @if(session('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 3500)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-16"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-16"
         style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;padding:0.75rem 1.1rem;color:#065f46;font-size:0.85rem;display:flex;align-items:center;gap:0.75rem;box-shadow:0 4px 16px rgba(0,0,0,0.12);min-width:220px;max-width:360px">
        <svg style="flex-shrink:0;width:16px;height:16px" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span style="flex:1">{{ session('success') }}</span>
        <button @click="show=false" style="background:none;border:none;cursor:pointer;color:#065f46;font-size:1.1rem;line-height:1;padding:0;opacity:0.6">&times;</button>
    </div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:0.75rem;margin-bottom:1.5rem">
        @foreach([
            ['label'=>'Total',        'val'=>$counts['total'],    'color'=>'#6366f1'],
            ['label'=>'Sin revisar',  'val'=>$counts['unseen'],   'color'=>'#f59e0b'],
            ['label'=>'Vendedor',  'val'=>$counts['vendedor'],  'color'=>'#3b82f6'],
            ['label'=>'Comprador', 'val'=>$counts['comprador'], 'color'=>'#10b981'],
            ['label'=>'B2B',       'val'=>$counts['b2b'],       'color'=>'#8b5cf6'],
            ['label'=>'Contacto',  'val'=>$counts['contacto'],  'color'=>'#64748b'],
        ] as $s)
        <div class="card" style="margin:0;padding:0.85rem;text-align:center">
            <p style="font-size:1.5rem;font-weight:800;color:{{ $s['color'] }};margin:0">{{ $s['val'] }}</p>
            <p style="font-size:0.72rem;color:var(--text-muted);margin:0.15rem 0 0">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap;align-items:center">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar nombre, email, teléfono..."
               class="form-input" style="flex:1;min-width:200px">
        <select wire:model.live="type" class="form-select" style="width:auto">
            <option value="">Todos los tipos</option>
            <option value="vendedor">Vendedor</option>
            <option value="comprador">Comprador</option>
            <option value="b2b">B2B</option>
            <option value="contacto">Contacto</option>
        </select>
        <select wire:model.live="status" class="form-select" style="width:auto">
            <option value="">Todos los estados</option>
            <option value="new">Nuevo</option>
            <option value="contacted">Contactado</option>
            <option value="qualified">Calificado</option>
            <option value="won">Ganado</option>
            <option value="lost">Perdido</option>
        </select>
        @if($search || $type || $status)
        <button wire:click="$set('search',''); $set('type',''); $set('status','')" class="btn btn-outline">
            Limpiar
        </button>
        @endif

        {{-- Indicador polling --}}
        <span style="margin-left:auto;font-size:0.72rem;color:var(--text-muted);display:flex;align-items:center;gap:0.35rem">
            <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block;animation:pulse-dot 2s infinite"></span>
            En vivo · actualiza cada 10s
        </span>
    </div>

    {{-- Bulk bar --}}
    @if(count($selected) > 0)
    <div style="background:#fef9ec;border:1px solid #fde68a;border-radius:var(--radius);padding:0.6rem 1rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.75rem">
        <span style="font-size:0.85rem;font-weight:600;color:#92400e">{{ count($selected) }} seleccionado{{ count($selected) !== 1 ? 's' : '' }}</span>
        <button wire:click="bulkDelete" wire:confirm="¿Eliminar los {{ count($selected) }} leads seleccionados?" class="btn btn-danger btn-sm">
            Eliminar seleccionados
        </button>
        <button wire:click="$set('selected', []); $set('selectAll', false)" class="btn btn-outline btn-sm">
            Cancelar
        </button>
    </div>
    @endif

    {{-- Table --}}
    <div class="card">
        @if($submissions->count() > 0)
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:36px">
                            <input type="checkbox" wire:model.live="selectAll" style="cursor:pointer" title="Seleccionar todos">
                        </th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Temperatura</th>
                        <th>Estado</th>
                        <th>Email / Teléfono</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                    @php
                        $typeColors   = ['vendedor'=>'badge-blue','comprador'=>'badge-green','b2b'=>'badge-yellow','contacto'=>''];
                        $statusColors = ['new'=>'badge-yellow','contacted'=>'badge-blue','qualified'=>'badge-green','won'=>'badge-green','lost'=>'badge-red'];
                        $statusLabels = ['new'=>'Nuevo','contacted'=>'Contactado','qualified'=>'Calificado','won'=>'Ganado','lost'=>'Perdido'];
                        $tempMeta = match($sub->lead_temperature) {
                            'hot'  => ['label'=>'🔥 Caliente', 'bg'=>'#fef2f2', 'color'=>'#b91c1c', 'border'=>'#fca5a5'],
                            'warm' => ['label'=>'☀ Templado',  'bg'=>'#fffbeb', 'color'=>'#b45309', 'border'=>'#fcd34d'],
                            'cold' => ['label'=>'❄ Frío',      'bg'=>'#eff6ff', 'color'=>'#1d4ed8', 'border'=>'#93c5fd'],
                            default=> ['label'=>'—',           'bg'=>'transparent', 'color'=>'var(--text-muted)', 'border'=>'transparent'],
                        };
                    @endphp
                    @php $unseen = !$sub->seen_at; @endphp
                    <tr wire:key="sub-{{ $sub->id }}" style="{{ in_array((string)$sub->id, $selected) ? 'background:rgba(99,102,241,0.06)' : ($unseen ? 'background:rgba(245,158,11,0.04)' : '') }}">
                        <td>
                            <input type="checkbox" wire:model.live="selected" value="{{ $sub->id }}" style="cursor:pointer">
                        </td>
                        <td style="font-weight:{{ $unseen ? '700' : '600' }}">
                            <div style="display:flex;align-items:center;gap:0.5rem">
                                @if($unseen)
                                <span title="No visto" style="width:8px;height:8px;border-radius:50%;background:#f59e0b;flex-shrink:0;box-shadow:0 0 0 2px rgba(245,158,11,0.25)"></span>
                                @endif
                                {{ $sub->full_name }}
                                @if($sub->client_id)
                                <span title="Convertido a cliente" style="font-size:0.65rem;font-weight:700;letter-spacing:0.03em;background:#d1fae5;color:#065f46;border-radius:4px;padding:1px 5px;border:1px solid #a7f3d0">CLI</span>
                                @endif
                            </div>
                        </td>
                        <td><span class="badge {{ $typeColors[$sub->form_type] ?? '' }}">{{ ucfirst($sub->form_type) }}</span></td>
                        <td>
                            <span style="display:inline-flex;align-items:center;font-size:0.78rem;font-weight:600;padding:3px 8px;border-radius:99px;background:{{ $tempMeta['bg'] }};color:{{ $tempMeta['color'] }};border:1px solid {{ $tempMeta['border'] }};white-space:nowrap">
                                {{ $tempMeta['label'] }}
                            </span>
                        </td>
                        <td><span class="badge {{ $statusColors[$sub->status] ?? '' }}">{{ $statusLabels[$sub->status] ?? $sub->status }}</span></td>
                        <td style="font-size:0.82rem">
                            <div>{{ $sub->email }}</div>
                            <div style="color:var(--text-muted)">{{ $sub->phone }}</div>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.82rem;white-space:nowrap">
                            {{ $sub->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <div style="display:flex;gap:0.4rem;align-items:center;flex-wrap:wrap">
                                <a href="{{ route('admin.form-submissions.show', $sub) }}" class="btn btn-outline btn-sm">Ver</a>
                                @if(!$sub->client_id)
                                <button wire:click="convertToClient({{ $sub->id }})"
                                        wire:confirm="¿Convertir «{{ $sub->full_name }}» a cliente?"
                                        wire:loading.attr="disabled"
                                        wire:target="convertToClient({{ $sub->id }})"
                                        class="btn btn-sm"
                                        style="background:#eff6ff;color:#1d4ed8;border:1px solid #93c5fd;font-size:0.78rem"
                                        title="Crear registro de cliente a partir de este lead">
                                    <span wire:loading.remove wire:target="convertToClient({{ $sub->id }})">→ Cliente</span>
                                    <span wire:loading wire:target="convertToClient({{ $sub->id }})">...</span>
                                </button>
                                @else
                                <span style="font-size:0.75rem;color:#059669;font-weight:600;display:inline-flex;align-items:center;gap:0.25rem">
                                    <svg style="width:12px;height:12px" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Cliente
                                </span>
                                @endif
                                <button wire:click="delete({{ $sub->id }})"
                                        wire:confirm="¿Eliminar este lead?"
                                        wire:loading.attr="disabled"
                                        wire:target="delete({{ $sub->id }})"
                                        class="btn btn-danger btn-sm">
                                    <span wire:loading.remove wire:target="delete({{ $sub->id }})">Eliminar</span>
                                    <span wire:loading wire:target="delete({{ $sub->id }})">...</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:0.75rem 1.2rem;border-top:1px solid var(--border)">
            {{ $submissions->links() }}
        </div>
        @else
        <div style="padding:3rem;text-align:center;color:var(--text-muted)">
            <p style="margin:0">
                @if($search || $type || $status)
                    No hay leads con esos filtros.
                @else
                    Aún no hay envíos. Los leads aparecerán aquí automáticamente.
                @endif
            </p>
        </div>
        @endif
    </div>

    <style>
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50%       { opacity: .3; }
        }
    </style>
</div>
