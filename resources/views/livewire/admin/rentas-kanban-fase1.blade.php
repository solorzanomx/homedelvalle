<div wire:loading.class="opacity-60" wire:loading.class.remove="opacity-100">

{{-- ── Toast de notificaciones ─────────────────────────────────────────────── --}}
<div id="kanban-toast" style="position:fixed;top:1.25rem;right:1.25rem;z-index:9999;display:none;max-width:320px;"
     x-data="{ show: false, msg: '', type: 'success' }"
     @kanban-success.window="msg = $event.detail[0].message; type='success'; show=true; setTimeout(()=>show=false,3500)"
     @kanban-error.window="msg = $event.detail[0].message; type='error'; show=true; setTimeout(()=>show=false,5000)"
     x-show="show" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0 translate-y-1"
     :style="type==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.85rem 1rem;box-shadow:0 4px 24px rgba(0,0,0,.12);' : 'background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.85rem 1rem;box-shadow:0 4px 24px rgba(0,0,0,.12);'">
    <p style="font-size:.8rem;font-weight:600;" :style="type==='success' ? 'color:#166534' : 'color:#991b1b'" x-text="msg"></p>
</div>

{{-- ── Stats strip ─────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem;">
    @foreach([
        ['Total', $stats['total'],    '#0f172a'],
        ['Esta semana', $stats['esta_sem'], '#1D4ED8'],
        ['Sin asignar', $stats['sin_asig'], $stats['sin_asig'] > 0 ? '#f59e0b' : '#0f172a'],
        ['SLA vencido', $stats['vencidos'], $stats['vencidos'] > 0 ? '#ef4444' : '#0f172a'],
    ] as [$lbl, $val, $col])
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.85rem 1.1rem;">
        <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.2rem;">{{ $lbl }}</p>
        <p style="font-size:1.6rem;font-weight:800;color:{{ $col }};">{{ $val }}</p>
    </div>
    @endforeach
</div>

{{-- ── Filtros ──────────────────────────────────────────────────────────────── --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.85rem 1.1rem;margin-bottom:1.1rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
    <div>
        <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.3rem;">Agente</label>
        <select wire:model.live="filtroAgente" style="border:1px solid #e2e8f0;border-radius:7px;padding:.35rem .75rem;font-size:.78rem;color:#0f172a;background:#fff;min-width:140px;">
            <option value="">Todos</option>
            @foreach($agentes as $ag)
            <option value="{{ $ag->id }}">{{ $ag->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.3rem;">Colonia</label>
        <input wire:model.live.debounce.400ms="filtroColonia" type="text" placeholder="Ej: Del Valle" style="border:1px solid #e2e8f0;border-radius:7px;padding:.35rem .75rem;font-size:.78rem;color:#0f172a;width:140px;">
    </div>
    <div style="display:flex;gap:.5rem;align-items:flex-end;">
        <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.3rem;">Renta mín.</label>
            <input wire:model.live.debounce.500ms="filtroRentaMin" type="number" placeholder="0" style="border:1px solid #e2e8f0;border-radius:7px;padding:.35rem .75rem;font-size:.78rem;width:90px;">
        </div>
        <div>
            <label style="display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.3rem;">Renta máx.</label>
            <input wire:model.live.debounce.500ms="filtroRentaMax" type="number" placeholder="∞" style="border:1px solid #e2e8f0;border-radius:7px;padding:.35rem .75rem;font-size:.78rem;width:90px;">
        </div>
    </div>
    @if($filtroAgente || $filtroColonia || $filtroRentaMin || $filtroRentaMax)
    <button wire:click="$set('filtroAgente',''); $set('filtroColonia',''); $set('filtroRentaMin',''); $set('filtroRentaMax','')"
            style="padding:.35rem .75rem;font-size:.75rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;cursor:pointer;align-self:flex-end;">
        ✕ Limpiar
    </button>
    @endif
</div>

{{-- ── Bulk actions ─────────────────────────────────────────────────────────── --}}
@if(!empty($selected))
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:.75rem 1.1rem;margin-bottom:1rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <span style="font-size:.78rem;font-weight:600;color:#1D4ED8;">{{ count($selected) }} seleccionada(s)</span>

    <select wire:model="bulkStage" style="border:1px solid #bfdbfe;border-radius:7px;padding:.3rem .65rem;font-size:.75rem;color:#0f172a;">
        <option value="">Mover a etapa…</option>
        @foreach(\App\Models\Operation::CAPTACION_STAGES as $s)
        <option value="{{ $s }}">{{ \App\Models\Operation::STAGES[$s] ?? $s }}</option>
        @endforeach
    </select>
    <button wire:click="bulkMoveStage" style="padding:.3rem .75rem;font-size:.75rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:7px;cursor:pointer;">Mover</button>

    <select wire:model="bulkAgente" style="border:1px solid #bfdbfe;border-radius:7px;padding:.3rem .65rem;font-size:.75rem;color:#0f172a;">
        <option value="">Asignar a agente…</option>
        @foreach($agentes as $ag)
        <option value="{{ $ag->id }}">{{ $ag->name }}</option>
        @endforeach
    </select>
    <button wire:click="bulkAssignAgent" style="padding:.3rem .75rem;font-size:.75rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:7px;cursor:pointer;">Asignar</button>

    <button wire:click="bulkMarkCold" style="padding:.3rem .75rem;font-size:.75rem;font-weight:600;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:7px;cursor:pointer;"
            onclick="return confirm('¿Marcar como frías?')">
        ❄️ Marcar frías
    </button>

    <button wire:click="$set('selected', [])" style="padding:.3rem .75rem;font-size:.72rem;color:#64748b;background:none;border:none;cursor:pointer;">✕ Cancelar</button>
</div>
@endif

{{-- ── Kanban board ─────────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:.65rem;overflow-x:auto;padding-bottom:1.25rem;align-items:flex-start;">
    @php
        $stageLabels = \App\Models\Operation::STAGES;
    @endphp
    @foreach(\App\Models\Operation::CAPTACION_STAGES as $stage)
    @php
        $cards     = $byStage[$stage] ?? [];
        $cardCount = count($cards);
        $hasOver   = collect($cards)->contains(fn($c) => $c['sla_color'] === 'red');
    @endphp
    <div style="min-width:210px;flex-shrink:0;">
        {{-- Header columna --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.45rem .7rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px 8px 0 0;border-bottom:2px solid {{ $hasOver ? '#ef4444' : '#3B82C4' }};">
            <span style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1e293b;">{{ $stageLabels[$stage] ?? $stage }}</span>
            <span style="background:{{ $hasOver ? '#fef2f2' : '#eff6ff' }};color:{{ $hasOver ? '#ef4444' : '#3B82C4' }};font-size:.62rem;font-weight:700;border-radius:9999px;padding:.1rem .45rem;">{{ $cardCount }}</span>
        </div>

        {{-- Cards —sortable-- --}}
        <div class="kanban-col"
             data-stage="{{ $stage }}"
             wire:ignore
             style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 8px 8px;min-height:80px;padding:.4rem;display:flex;flex-direction:column;gap:.4rem;">

            @forelse($cards as $card)
            <div class="kanban-card"
                 data-id="{{ $card['id'] }}"
                 data-stage="{{ $stage }}"
                 style="background:#fff;border:1px solid #e2e8f0;border-left:3px solid {{ $card['sla_color'] === 'green' ? '#10b981' : ($card['sla_color'] === 'yellow' ? '#f59e0b' : '#ef4444') }};border-radius:7px;padding:.65rem .75rem;cursor:grab;user-select:none;transition:box-shadow .15s;"
                 onmouseover="this.style.boxShadow='0 2px 10px rgba(0,0,0,.1)'"
                 onmouseout="this.style.boxShadow='none'">

                {{-- Checkbox + nombre --}}
                <div style="display:flex;align-items:flex-start;gap:.5rem;">
                    <input type="checkbox"
                           wire:model.live="selected"
                           value="{{ $card['id'] }}"
                           style="margin-top:.15rem;flex-shrink:0;cursor:pointer;"
                           onclick="event.stopPropagation()">
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('admin.rentas.gestion.show', $card['id']) }}"
                           style="font-size:.78rem;font-weight:600;color:#0f172a;text-decoration:none;line-height:1.3;display:block;"
                           onclick="event.stopPropagation()">
                            {{ Str::limit($card['client_name'], 22) }}
                        </a>
                        <p style="font-size:.68rem;color:#64748b;margin-top:.1rem;">{{ $card['colony'] }}</p>
                    </div>
                </div>

                {{-- Datos --}}
                <div style="display:flex;flex-wrap:wrap;gap:.3rem .6rem;margin-top:.5rem;">
                    @if($card['rent'])
                    <span style="font-size:.65rem;font-weight:600;color:#1D4ED8;">${{ number_format($card['rent']) }}/mes</span>
                    @endif
                    @if($card['area'])
                    <span style="font-size:.65rem;color:#64748b;">{{ $card['area'] }} m²</span>
                    @endif
                </div>

                {{-- Footer: agente + SLA --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.45rem;">
                    <span style="font-size:.62rem;color:#94a3b8;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100px;">
                        {{ $card['agent'] ? Str::before($card['agent'], ' ') : '—' }}
                    </span>
                    <span title="SLA: {{ $card['sla_pct'] }}% — {{ $card['days'] }} día(s)"
                          style="font-size:.6rem;font-weight:700;padding:.1rem .4rem;border-radius:9999px;background:{{ $card['sla_color'] === 'green' ? '#f0fdf4' : ($card['sla_color'] === 'yellow' ? '#fffbeb' : '#fef2f2') }};color:{{ $card['sla_color'] === 'green' ? '#166534' : ($card['sla_color'] === 'yellow' ? '#92400e' : '#991b1b') }};">
                        {{ $card['days'] }}d
                    </span>
                </div>
            </div>
            @empty
            <p style="font-size:.68rem;color:#cbd5e1;text-align:center;padding:.85rem 0;">Vacío</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

</div>

{{-- ── SortableJS + Livewire bridge ────────────────────────────────────────── --}}
<script>
document.addEventListener('livewire:initialized', () => { initKanban(); });
document.addEventListener('livewire:updated',     () => { initKanban(); });

function initKanban() {
    document.querySelectorAll('.kanban-col').forEach(col => {
        if (col._sortable) col._sortable.destroy();

        col._sortable = Sortable.create(col, {
            group:     'captaciones',
            animation: 150,
            ghostClass: 'kanban-ghost',
            dragClass:  'kanban-drag',
            handle:     '.kanban-card',
            onEnd(evt) {
                const cardEl    = evt.item;
                const toCol     = evt.to;
                const fromCol   = evt.from;
                const opId      = parseInt(cardEl.dataset.id);
                const newStage  = toCol.dataset.stage;
                const oldStage  = cardEl.dataset.stage;

                if (newStage === oldStage) return;

                // Actualizar data-stage del card para futuros moves
                cardEl.dataset.stage = newStage;

                // Notificar a Livewire
                Livewire.dispatch('card-moved', {
                    operationId: opId,
                    newStage:    newStage,
                    oldStage:    oldStage,
                });
            }
        });
    });
}
</script>
<style>
.kanban-ghost { opacity: .35; border: 2px dashed #3B82C4 !important; }
.kanban-drag  { box-shadow: 0 8px 24px rgba(0,0,0,.15) !important; transform: rotate(1.5deg); }
</style>
