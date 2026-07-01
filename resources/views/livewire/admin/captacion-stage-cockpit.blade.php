<div>
    @php
        $client = $captacion->client;
        $phone = $client->whatsapp ?? $client->phone ?? '';
        $waLink = $phone ? 'https://wa.me/52' . preg_replace('/\D/', '', $phone) : '';
        $telLink = $phone ? 'tel:' . $phone : '';
    @endphp

    @foreach($stageKeys as $stageIdx => $stageKey)
        @php
            $stageLabel = \App\Models\Operation::STAGES[$stageKey] ?? $stageKey;
            $stageItems = $itemsByStage->get($stageKey, collect())->sortBy('id');
            $stageCompleted = $stageItems->where('is_completed', true)->count();
            $stageTotal = $stageItems->count();
            $stageColor = \App\Models\Operation::STAGE_COLORS[$stageKey] ?? '#94a3b8';
            $isPast = $stageIdx < $currentIdx;
            $isCurrent = $stageIdx === $currentIdx;
            $isFuture = $stageIdx > $currentIdx;
            $goal = \App\Livewire\Admin\CaptacionStageCockpit::STAGE_OBJECTIVES[$stageKey] ?? null;
        @endphp

        <div class="stage-checklist-group">
            <div class="stage-checklist-header">
                <div class="stage-label">
                    <span class="stage-dot" style="background: {{ $stageColor }};"></span>
                    {{ $stageLabel }}
                    @if($isPast) <span style="font-size:0.7rem;color:var(--success);">&#10003;</span> @endif
                    @if($isCurrent) <span class="badge" style="background:{{ $stageColor }}1a;color:{{ $stageColor }};font-size:.65rem;">Etapa actual</span> @endif
                </div>
                <span class="stage-count">
                    @if($stageTotal > 0) {{ $stageCompleted }}/{{ $stageTotal }} @else — @endif
                </span>
            </div>

            @if($isCurrent)
                <div style="padding-left:.5rem;">
                    @if($goal)
                    <div class="cockpit-header">
                        <div class="cockpit-goal">&#127919; {{ $goal }}</div>
                    </div>
                    @endif

                    @foreach($stageItems as $item)
                        @php
                            $actionType = $item->template->action_type ?? null;
                            $key = $item->id;
                            $isEditing = $editingItemId === $item->id;
                            $showExpanded = !$item->is_completed || $isEditing;
                        @endphp

                        <div class="cockpit-item {{ $item->is_completed && !$isEditing ? 'done' : '' }}">
                            <div class="cockpit-title">
                                @if($item->is_completed && !$isEditing)
                                    <span style="color:var(--success);">&#10003;</span>
                                @else
                                    <span class="stage-dot" style="background:{{ $stageColor }};"></span>
                                @endif
                                {{ $item->template->title ?? 'Ítem' }}
                                @if(!$item->template->is_required)
                                    <span style="font-size:.65rem;color:var(--text-muted);font-weight:400;">(opcional)</span>
                                @endif
                            </div>

                            @if($item->is_completed && !$isEditing)
                                {{-- Colapsado: resumen + editar --}}
                                @if($actionType === 'confirmar_interes')
                                    <div class="cockpit-summary">Motivo: {{ $captacion->motivo ?? '—' }} &middot; Urgencia: {{ $captacion->urgencia ?? '—' }}</div>
                                @elseif($actionType === 'datos_inmueble')
                                    <div class="cockpit-summary">{{ $captacion->property?->address ?? '—' }} &middot; {{ \App\Livewire\Admin\CaptacionStageCockpit::TIPO_OPTIONS[$captacion->property?->property_type] ?? $captacion->property?->property_type }} &middot; {{ $captacion->property?->area ?? '—' }} m&sup2;</div>
                                @elseif($actionType === 'llamar' && !empty($formData[$key]['nota']))
                                    <div class="cockpit-summary">"{{ $formData[$key]['nota'] }}"</div>
                                @elseif($item->completedByUser)
                                    <div class="cockpit-summary">{{ $item->completedByUser->name }} &middot; {{ $item->completed_at?->format('d/m H:i') }}</div>
                                @endif

                                @if(in_array($actionType, ['confirmar_interes', 'datos_inmueble', 'llamar']))
                                    <button type="button" class="cockpit-edit-link" wire:click="startEdit({{ $item->id }})">Editar</button>
                                @endif
                            @endif

                            @if($showExpanded)
                                <div class="cockpit-body">
                                    @if($actionType === 'llamar')
                                        <div class="cockpit-actions">
                                            @if($telLink)<a href="{{ $telLink }}" class="action-btn phone">&#128222; Llamar ahora</a>@endif
                                            @if($waLink)<a href="{{ $waLink }}" target="_blank" class="action-btn wa">&#128172; WhatsApp</a>@endif
                                        </div>
                                        <div class="cockpit-field">
                                            <label>¿Qué te dijo? (opcional)</label>
                                            <textarea class="form-input" rows="2" wire:model="formData.{{ $key }}.nota" placeholder="Ej: Sí contestó, interesado, ya sabe precio de referencia"></textarea>
                                        </div>
                                        <div class="cockpit-actions">
                                            <button type="button" class="btn btn-primary btn-sm" wire:click="completeLlamada({{ $item->id }})">Listo, ya llamé</button>
                                            @if($isEditing)<button type="button" class="cockpit-edit-link" wire:click="cancelEdit">Cancelar</button>@endif
                                        </div>

                                    @elseif($actionType === 'confirmar_interes')
                                        <div class="cockpit-field">
                                            <label>Motivo</label>
                                            <select class="form-select" wire:model="formData.{{ $key }}.motivo">
                                                <option value="">Selecciona...</option>
                                                @foreach(\App\Livewire\Admin\CaptacionStageCockpit::MOTIVO_OPTIONS as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                            @error("formData.{$key}.motivo") <div style="color:#ef4444;font-size:.7rem;margin-top:.2rem;">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="cockpit-field">
                                            <label>Urgencia</label>
                                            <select class="form-select" wire:model="formData.{{ $key }}.urgencia">
                                                <option value="">Selecciona...</option>
                                                @foreach(\App\Livewire\Admin\CaptacionStageCockpit::URGENCIA_OPTIONS as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="cockpit-field">
                                            <label>Nota (opcional)</label>
                                            <textarea class="form-input" rows="2" wire:model="formData.{{ $key }}.nota"></textarea>
                                        </div>
                                        <div class="cockpit-actions">
                                            <button type="button" class="btn btn-primary btn-sm" wire:click="saveInteres({{ $item->id }})">Guardar y marcar como hecho</button>
                                            @if($isEditing)<button type="button" class="cockpit-edit-link" wire:click="cancelEdit">Cancelar</button>@endif
                                        </div>

                                    @elseif($actionType === 'datos_inmueble')
                                        <div class="cockpit-field">
                                            <label>Dirección</label>
                                            <input type="text" class="form-control" wire:model="formData.{{ $key }}.direccion" placeholder="Calle, número, colonia">
                                            @error("formData.{$key}.direccion") <div style="color:#ef4444;font-size:.7rem;margin-top:.2rem;">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="cockpit-field">
                                            <label>Tipo de inmueble</label>
                                            <select class="form-select" wire:model="formData.{{ $key }}.tipo">
                                                <option value="">Selecciona...</option>
                                                @foreach(\App\Livewire\Admin\CaptacionStageCockpit::TIPO_OPTIONS as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="cockpit-field">
                                            <label>m&sup2; aproximados</label>
                                            <input type="number" class="form-control" wire:model="formData.{{ $key }}.m2" placeholder="Ej: 90">
                                        </div>
                                        <div class="cockpit-actions">
                                            <button type="button" class="btn btn-primary btn-sm" wire:click="saveDatosInmueble({{ $item->id }})">Guardar y marcar como hecho</button>
                                            @if($isEditing)<button type="button" class="cockpit-edit-link" wire:click="cancelEdit">Cancelar</button>@endif
                                        </div>

                                    @else
                                        {{-- Fallback: checkbox manual de siempre (título ya se muestra arriba) --}}
                                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.8rem;">
                                            <input type="checkbox" wire:click="toggleManual({{ $item->id }})" {{ $item->is_completed ? 'checked' : '' }}>
                                            Marcar como hecho
                                        </label>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif($stageTotal > 0)
                <div style="padding-left:.5rem;">
                    @foreach($stageItems as $item)
                    <div class="checklist-item {{ $item->is_completed ? 'completed' : '' }} {{ $isPast ? 'past' : '' }} {{ $isFuture ? 'locked' : '' }}">
                        <input type="checkbox" {{ $item->is_completed || $isPast ? 'checked' : '' }} disabled>
                        <label>{{ $item->template->title ?? 'Item' }}</label>
                    </div>
                    @if($item->is_completed && $item->completedByUser)
                    <div class="checklist-meta">{{ $item->completedByUser->name }} &middot; {{ $item->completed_at->format('d/m H:i') }}</div>
                    @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
