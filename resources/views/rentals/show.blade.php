@extends('layouts.app-sidebar')
@section('title', 'Renta #' . $rental->id)

@section('styles')
<style>
.rental-layout { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; }
.rental-sidebar .card { position: sticky; top: 72px; }

/* Stage progress */
.stage-progress { padding: 1rem 0; }
.stage-progress-bar {
    display: flex; gap: 3px; margin-bottom: 0.5rem;
}
.stage-segment {
    flex: 1; height: 6px; border-radius: 3px; background: var(--border);
    transition: background 0.3s;
}
.stage-segment.completed { background: var(--primary); }
.stage-segment.current { background: var(--primary); opacity: 0.6; }
.stage-current-label {
    font-size: 0.82rem; font-weight: 600; color: var(--text);
    display: flex; align-items: center; gap: 0.4rem;
}
.stage-dot { width: 10px; height: 10px; border-radius: 50%; }

/* Detail rows */
.detail-rows { padding: 0.75rem 0; }
.detail-row { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.82rem; }
.detail-row .label { color: var(--text-muted); }
.detail-row .value { font-weight: 500; text-align: right; max-width: 60%; }

/* Quick actions */
.quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.75rem; }
.quick-actions .btn { justify-content: center; font-size: 0.8rem; padding: 0.5rem 0.75rem; }

/* Timeline */
.timeline { position: relative; padding-left: 24px; }
.timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: var(--border); }
.timeline-item { position: relative; margin-bottom: 1.25rem; }
.timeline-dot {
    position: absolute; left: -20px; top: 4px; width: 14px; height: 14px; border-radius: 50%;
    border: 2px solid var(--card); z-index: 1;
}
.timeline-content { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 0.75rem 1rem; }
.timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem; }
.timeline-type { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.timeline-date { font-size: 0.72rem; color: var(--text-muted); }
.timeline-body { font-size: 0.85rem; line-height: 1.5; }
.timeline-meta { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.35rem; }

/* Tabs */
.tab-bar { display: flex; gap: 0; border-bottom: 2px solid var(--border); margin-bottom: 1.25rem; }
.tab-btn { padding: 0.6rem 1.1rem; font-size: 0.85rem; font-weight: 500; color: var(--text-muted); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.15s; }
.tab-btn:hover { color: var(--text); }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* Document list */
.doc-item {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem;
    border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 0.5rem;
    background: var(--card);
}
.doc-icon { font-size: 1.5rem; flex-shrink: 0; }
.doc-info { flex: 1; overflow: hidden; }
.doc-name { font-size: 0.85rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doc-meta { font-size: 0.72rem; color: var(--text-muted); }
.doc-actions { display: flex; gap: 0.25rem; flex-shrink: 0; }

/* Stage change form */
.stage-change-form { display: flex; gap: 0.5rem; align-items: flex-end; }
.stage-change-form .form-group { margin-bottom: 0; flex: 1; }

@media (max-width: 1024px) { .rental-layout { grid-template-columns: 1fr; } .rental-sidebar .card { position: static; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $rental->property->title ?? 'Renta #' . $rental->id }}</h2>
        <p class="text-muted">Proceso de arrendamiento</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('rentals.edit', $rental) }}" class="btn btn-outline">&#9998; Editar</a>
        <a href="{{ route('rentals.index') }}" class="btn btn-outline">&#8592; Rentas</a>
    </div>
</div>

<div class="rental-layout">
    {{-- LEFT: Sidebar --}}
    <div class="rental-sidebar">
        <div class="card">
            <div class="card-body">
                {{-- Stage Progress --}}
                <div class="stage-progress">
                    @php
                        $stageKeys = array_keys(\App\Models\RentalProcess::STAGES);
                        $currentIdx = array_search($rental->stage, $stageKeys);
                    @endphp
                    <div class="stage-progress-bar">
                        @foreach($stageKeys as $i => $sk)
                            <div class="stage-segment {{ $i < $currentIdx ? 'completed' : ($i === $currentIdx ? 'current' : '') }}"></div>
                        @endforeach
                    </div>
                    <div class="stage-current-label">
                        <span class="stage-dot" style="background: {{ $rental->stage_color }};"></span>
                        {{ $rental->stage_label }}
                    </div>
                </div>

                {{-- Quick Stage Change --}}
                <form method="POST" action="{{ route('rentals.update-stage', $rental->id) }}" class="stage-change-form" style="margin-bottom:1rem;">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label" style="font-size:0.75rem;">Cambiar etapa</label>
                        <select name="stage" class="form-select" style="font-size:0.82rem;">
                            @foreach(\App\Models\RentalProcess::STAGES as $sk => $sl)
                                <option value="{{ $sk }}" {{ $rental->stage === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary" style="margin-bottom:0;">Cambiar</button>
                </form>

                {{-- Detail Rows --}}
                <div class="detail-rows" style="border-top:1px solid var(--border);">
                    @if($rental->property)
                    <div class="detail-row">
                        <span class="label">Propiedad</span>
                        <span class="value"><a href="{{ route('properties.show', $rental->property_id) }}" style="color:var(--primary);">{{ Str::limit($rental->property->title, 25) }}</a></span>
                    </div>
                    @endif
                    @if($rental->ownerClient)
                    <div class="detail-row">
                        <span class="label">Propietario</span>
                        <span class="value"><a href="{{ route('clients.show', $rental->owner_client_id) }}" style="color:var(--primary);">{{ $rental->ownerClient->name }}</a></span>
                    </div>
                    @endif
                    @if($rental->tenantClient)
                    <div class="detail-row">
                        <span class="label">Inquilino</span>
                        <span class="value"><a href="{{ route('clients.show', $rental->tenant_client_id) }}" style="color:var(--primary);">{{ $rental->tenantClient->name }}</a></span>
                    </div>
                    @endif
                    @if($rental->broker)
                    <div class="detail-row">
                        <span class="label">Broker</span>
                        <span class="value">{{ $rental->broker->name }}</span>
                    </div>
                    @endif
                    @if($rental->monthly_rent)
                    <div class="detail-row">
                        <span class="label">Renta Mensual</span>
                        <span class="value">{{ $rental->currency ?? 'MXN' }} ${{ number_format($rental->monthly_rent, 0) }}</span>
                    </div>
                    @endif
                    @if($rental->deposit_amount)
                    <div class="detail-row">
                        <span class="label">Deposito</span>
                        <span class="value">${{ number_format($rental->deposit_amount, 0) }}</span>
                    </div>
                    @endif
                    @if($rental->guarantee_type)
                    <div class="detail-row">
                        <span class="label">Garantia</span>
                        <span class="value">{{ $rental->guarantee_type_label }}</span>
                    </div>
                    @endif
                    @if($rental->commission_amount || $rental->commission_percentage)
                    <div class="detail-row">
                        <span class="label">Comision</span>
                        <span class="value">
                            @if($rental->commission_amount) ${{ number_format($rental->commission_amount, 0) }} @endif
                            @if($rental->commission_percentage) ({{ $rental->commission_percentage }}%) @endif
                        </span>
                    </div>
                    @endif
                    @if($rental->lease_start_date)
                    <div class="detail-row">
                        <span class="label">Inicio</span>
                        <span class="value">{{ $rental->lease_start_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($rental->lease_end_date)
                    <div class="detail-row">
                        <span class="label">Fin</span>
                        <span class="value">
                            {{ $rental->lease_end_date->format('d/m/Y') }}
                            @if($rental->days_until_expiration !== null)
                                @if($rental->is_expired)
                                    <span class="badge badge-red" style="margin-left:3px;">Vencido</span>
                                @elseif($rental->days_until_expiration <= 30)
                                    <span class="badge badge-yellow" style="margin-left:3px;">{{ $rental->days_until_expiration }}d</span>
                                @endif
                            @endif
                        </span>
                    </div>
                    @endif
                    @if($rental->lease_duration_months)
                    <div class="detail-row">
                        <span class="label">Duracion</span>
                        <span class="value">{{ $rental->lease_duration_months }} meses</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="label">Creado</span>
                        <span class="value">{{ $rental->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                @if($rental->notes)
                <div style="border-top:1px solid var(--border); padding-top:0.75rem; margin-top:0.25rem;">
                    <div style="font-size:0.75rem; font-weight:600; color:var(--text-muted); margin-bottom:0.25rem;">NOTAS</div>
                    <div style="font-size:0.82rem; line-height:1.5;">{!! \App\Helpers\MentionHelper::render($rental->notes) !!}</div>
                </div>
                @endif

                {{-- Stats --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; padding-top:0.75rem; border-top:1px solid var(--border); text-align:center; margin-top:0.75rem;">
                    <div>
                        <div style="font-size:1.1rem; font-weight:700;">{{ $rental->documents->count() }}</div>
                        <div style="font-size:0.7rem; color:var(--text-muted);">Docs</div>
                    </div>
                    <div>
                        <div style="font-size:1.1rem; font-weight:700;">{{ $rental->tasks->count() }}</div>
                        <div style="font-size:0.7rem; color:var(--text-muted);">Tareas</div>
                    </div>
                    <div>
                        <div style="font-size:1.1rem; font-weight:700;">{{ $rental->stageLogs->count() }}</div>
                        <div style="font-size:0.7rem; color:var(--text-muted);">Cambios</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Tabs --}}
    <div>
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('timeline')">Timeline</button>
            <button class="tab-btn" onclick="switchTab('documents')">Documentos ({{ $rental->documents->count() }})</button>
            <button class="tab-btn" onclick="switchTab('poliza')">Poliza</button>
            <button class="tab-btn" onclick="switchTab('contracts')">Contratos ({{ $rental->contracts->count() }})</button>
            <button class="tab-btn" onclick="switchTab('tasks')">Tareas ({{ $rental->tasks->count() }})</button>
        </div>

        {{-- TAB: Timeline --}}
        <div class="tab-content active" id="tab-timeline">
            @if($timeline->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p style="font-size:2rem; margin-bottom:0.5rem;">&#128221;</p>
                    <p>Sin actividad registrada.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($timeline as $event)
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background: {{ $event['color'] }};"></div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="timeline-type" style="color: {{ $event['color'] }};">{{ $event['type_label'] }}</span>
                                <span class="timeline-date">{{ $event['date']->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="timeline-body">{!! $event['body'] !!}</div>
                            @if(!empty($event['meta']))
                            <div class="timeline-meta">{!! $event['meta'] !!}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB: Documents --}}
        <div class="tab-content" id="tab-documents">
            {{-- Upload Form --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-body" style="padding:1rem;">
                    <h4 style="font-size:0.85rem; font-weight:600; margin-bottom:0.75rem;">Subir Documento</h4>
                    <form method="POST" action="{{ route('rentals.documents.store', $rental->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Categoria</label>
                                <select name="category" class="form-select" required>
                                    @foreach($documentCategories as $catKey => $catLabel)
                                        <option value="{{ $catKey }}">{{ $catLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Etiqueta</label>
                                <input type="text" name="label" class="form-input" placeholder="Nombre del documento" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Archivo</label>
                                <input type="file" name="file" class="form-input" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            </div>
                            <div class="form-group" style="display:flex; align-items:flex-end;">
                                <button type="submit" class="btn btn-primary" style="width:100%;">Subir</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Document Checklist by Category --}}
            @php
                $docsByCategory = $rental->documents->groupBy('category');
            @endphp

            @foreach($documentCategories as $catKey => $catLabel)
                @php $catDocs = $docsByCategory->get($catKey, collect()); @endphp
                <div style="margin-bottom:0.75rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.35rem;">
                        @if($catDocs->where('status', 'verified')->count() > 0)
                            <span style="color:var(--success); font-size:1rem;">&#10003;</span>
                        @elseif($catDocs->count() > 0)
                            <span style="color:#f59e0b; font-size:1rem;">&#9679;</span>
                        @else
                            <span style="color:var(--border); font-size:1rem;">&#9675;</span>
                        @endif
                        <span style="font-size:0.82rem; font-weight:600;">{{ $catLabel }}</span>
                        <span style="font-size:0.72rem; color:var(--text-muted);">({{ $catDocs->count() }})</span>
                    </div>

                    @foreach($catDocs as $doc)
                    <div class="doc-item">
                        <div class="doc-icon">&#128196;</div>
                        <div class="doc-info">
                            <div class="doc-name">{{ $doc->label }}</div>
                            <div class="doc-meta">
                                {{ $doc->file_name }} &middot; {{ $doc->file_size_formatted }}
                                &middot; {{ $doc->uploader->name ?? '' }}
                                &middot; {{ $doc->created_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <span class="badge badge-{{ match($doc->status) { 'verified' => 'green', 'rejected' => 'red', 'received' => 'blue', default => 'yellow' } }}">
                            {{ $doc->status_label }}
                        </span>
                        <div class="doc-actions">
                            <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline" title="Descargar">&#8615;</a>
                            @if($doc->status !== 'verified')
                            <form method="POST" action="{{ route('documents.update-status', $doc->id) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="verified">
                                <button type="submit" class="btn btn-sm btn-outline" title="Verificar" style="color:var(--success);">&#10003;</button>
                            </form>
                            @endif
                            @if($doc->status !== 'rejected')
                            <form method="POST" action="{{ route('documents.update-status', $doc->id) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-outline" title="Rechazar" style="color:var(--danger);">&#10007;</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('documents.destroy', $doc->id) }}" style="display:inline;" onsubmit="return confirm('Eliminar este documento?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">&#128465;</button>
                            </form>
                        </div>
                    </div>
                    @if($doc->status === 'rejected' && $doc->rejection_reason)
                        <div style="margin-left:2.5rem; margin-bottom:0.5rem; font-size:0.78rem; color:var(--danger);">
                            Razon: {{ $doc->rejection_reason }}
                        </div>
                    @endif
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- TAB: Poliza Juridica --}}
        <div class="tab-content" id="tab-poliza">
            @php $poliza = $rental->poliza; @endphp

            @if(!$poliza)
                {{-- Create Poliza Form --}}
                <div class="card">
                    <div class="card-body" style="text-align:center; padding:2rem;">
                        <p style="font-size:1.5rem; margin-bottom:0.5rem;">&#128203;</p>
                        <p style="color:var(--text-muted); margin-bottom:1rem;">No hay poliza juridica asociada a este proceso.</p>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('polizaCreateForm').style.display='block'; this.style.display='none';">+ Crear Poliza Juridica</button>

                        <div id="polizaCreateForm" style="display:none; text-align:left; margin-top:1.25rem;">
                            <form method="POST" action="{{ route('rentals.poliza.store', $rental->id) }}">
                                @csrf
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Compania de Seguros</label>
                                        <input type="text" name="insurance_company" class="form-input" placeholder="Ej: Juridica Integral">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Numero de Poliza</label>
                                        <input type="text" name="policy_number" class="form-input" placeholder="Opcional">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Costo</label>
                                        <input type="number" name="cost" class="form-input" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Moneda</label>
                                        <select name="currency" class="form-select">
                                            <option value="MXN">MXN</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Notas</label>
                                        <textarea name="notes" class="form-textarea" rows="2" placeholder="Notas sobre la poliza..."></textarea>
                                    </div>
                                </div>
                                <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:0.5rem;">
                                    <button type="button" class="btn btn-outline" onclick="document.getElementById('polizaCreateForm').style.display='none'; this.closest('.card-body').querySelector('.btn-primary').style.display='';">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Crear Poliza</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                {{-- Poliza Details --}}
                <div class="card" style="margin-bottom:1rem;">
                    <div class="card-header">
                        <h3>Poliza Juridica</h3>
                        <span class="badge" style="background: {{ $poliza->status_color . '20' }}; color: {{ $poliza->status_color }};">{{ $poliza->status_label }}</span>
                    </div>
                    <div class="card-body">
                        {{-- Status Progress --}}
                        @php
                            $polizaStatuses = array_keys(\App\Models\PolizaJuridica::STATUSES);
                            $currentPolizaIdx = array_search($poliza->status, $polizaStatuses);
                        @endphp
                        <div style="display:flex; gap:3px; margin-bottom:1rem;">
                            @foreach($polizaStatuses as $pi => $ps)
                                @if(!in_array($ps, ['rejected', 'expired']))
                                <div style="flex:1; height:6px; border-radius:3px; background: {{ $pi <= $currentPolizaIdx && !in_array($poliza->status, ['rejected','expired']) ? $poliza->status_color : 'var(--border)' }};"></div>
                                @endif
                            @endforeach
                        </div>

                        <div class="form-grid">
                            <div class="detail-row" style="padding:0;">
                                <span class="label">Compania</span>
                                <span class="value">{{ $poliza->insurance_company ?? '—' }}</span>
                            </div>
                            <div class="detail-row" style="padding:0;">
                                <span class="label">No. Poliza</span>
                                <span class="value">{{ $poliza->policy_number ?? '—' }}</span>
                            </div>
                            @if($poliza->tenantClient)
                            <div class="detail-row" style="padding:0;">
                                <span class="label">Inquilino</span>
                                <span class="value">{{ $poliza->tenantClient->name }}</span>
                            </div>
                            @endif
                            @if($poliza->cost)
                            <div class="detail-row" style="padding:0;">
                                <span class="label">Costo</span>
                                <span class="value">{{ $poliza->currency }} ${{ number_format($poliza->cost, 0) }}</span>
                            </div>
                            @endif
                            @if($poliza->coverage_start)
                            <div class="detail-row" style="padding:0;">
                                <span class="label">Cobertura Inicio</span>
                                <span class="value">{{ $poliza->coverage_start->format('d/m/Y') }}</span>
                            </div>
                            @endif
                            @if($poliza->coverage_end)
                            <div class="detail-row" style="padding:0;">
                                <span class="label">Cobertura Fin</span>
                                <span class="value">{{ $poliza->coverage_end->format('d/m/Y') }}</span>
                            </div>
                            @endif
                            @if($poliza->rejection_reason)
                            <div class="detail-row full-width" style="padding:0;">
                                <span class="label">Razon Rechazo</span>
                                <span class="value" style="color:var(--danger);">{{ $poliza->rejection_reason }}</span>
                            </div>
                            @endif
                        </div>

                        @if($poliza->notes)
                        <div style="margin-top:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border); font-size:0.85rem;">
                            {{ $poliza->notes }}
                        </div>
                        @endif

                        {{-- Status Change Buttons --}}
                        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--border);">
                            @php
                                $transitions = match($poliza->status) {
                                    'pending' => ['documents_submitted'],
                                    'documents_submitted' => ['in_review', 'rejected'],
                                    'in_review' => ['approved', 'rejected'],
                                    'rejected' => ['pending'],
                                    'approved' => ['expired'],
                                    default => [],
                                };
                            @endphp
                            @foreach($transitions as $nextStatus)
                            <form method="POST" action="{{ route('polizas.update-status', $poliza->id) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $nextStatus }}">
                                @if($nextStatus === 'rejected')
                                    <input type="hidden" name="rejection_reason" value="" id="rejectReason-{{ $nextStatus }}">
                                @endif
                                <button type="submit" class="btn btn-sm {{ $nextStatus === 'approved' ? 'btn-primary' : ($nextStatus === 'rejected' ? 'btn-danger' : 'btn-outline') }}"
                                    @if($nextStatus === 'rejected') onclick="var r = prompt('Razon del rechazo:'); if(!r) { event.preventDefault(); return; } document.getElementById('rejectReason-{{ $nextStatus }}').value = r;" @endif
                                >
                                    {{ \App\Models\PolizaJuridica::STATUSES[$nextStatus] }}
                                </button>
                            </form>
                            @endforeach

                            <a href="#" onclick="event.preventDefault(); document.getElementById('polizaEditForm').style.display = document.getElementById('polizaEditForm').style.display === 'none' ? 'block' : 'none';" class="btn btn-sm btn-outline" style="margin-left:auto;">&#9998; Editar</a>
                        </div>

                        {{-- Edit Poliza Form (hidden) --}}
                        <div id="polizaEditForm" style="display:none; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
                            <form method="POST" action="{{ route('polizas.update', $poliza->id) }}">
                                @csrf @method('PUT')
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label">Compania</label>
                                        <input type="text" name="insurance_company" value="{{ $poliza->insurance_company }}" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">No. Poliza</label>
                                        <input type="text" name="policy_number" value="{{ $poliza->policy_number }}" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Costo</label>
                                        <input type="number" name="cost" value="{{ $poliza->cost }}" class="form-input" step="0.01" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Moneda</label>
                                        <select name="currency" class="form-select">
                                            <option value="MXN" {{ $poliza->currency === 'MXN' ? 'selected' : '' }}>MXN</option>
                                            <option value="USD" {{ $poliza->currency === 'USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Cobertura Inicio</label>
                                        <input type="date" name="coverage_start" value="{{ $poliza->coverage_start?->format('Y-m-d') }}" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Cobertura Fin</label>
                                        <input type="date" name="coverage_end" value="{{ $poliza->coverage_end?->format('Y-m-d') }}" class="form-input">
                                    </div>
                                    <div class="form-group full-width">
                                        <label class="form-label">Notas</label>
                                        <textarea name="notes" class="form-textarea" rows="2">{{ $poliza->notes }}</textarea>
                                    </div>
                                </div>
                                <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                                    <button type="button" class="btn btn-outline" onclick="document.getElementById('polizaEditForm').style.display='none';">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Poliza Events Timeline --}}
                <div class="card">
                    <div class="card-header">
                        <h3>Historial de Poliza</h3>
                    </div>
                    <div class="card-body">
                        {{-- Add Note --}}
                        <form method="POST" action="{{ route('polizas.events.store', $poliza->id) }}" style="display:flex; gap:0.5rem; margin-bottom:1rem;">
                            @csrf
                            <input type="text" name="description" class="form-input" placeholder="Agregar nota a la poliza..." required style="flex:1;">
                            <button type="submit" class="btn btn-primary">Agregar</button>
                        </form>

                        @if($poliza->events->isEmpty())
                            <p style="text-align:center; color:var(--text-muted); font-size:0.85rem; padding:1rem;">Sin eventos registrados.</p>
                        @else
                            <div class="timeline">
                                @foreach($poliza->events as $evt)
                                <div class="timeline-item">
                                    <div class="timeline-dot" style="background: {{ match($evt->event_type) { 'status_change' => '#8b5cf6', 'note' => '#3b82f6', 'created' => '#10b981', default => '#94a3b8' } }};"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <span class="timeline-type" style="color: {{ match($evt->event_type) { 'status_change' => '#8b5cf6', 'note' => '#3b82f6', 'created' => '#10b981', default => '#94a3b8' } }};">
                                                {{ match($evt->event_type) { 'status_change' => 'Cambio Estado', 'note' => 'Nota', 'created' => 'Creacion', default => ucfirst($evt->event_type) } }}
                                            </span>
                                            <span class="timeline-date">{{ $evt->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="timeline-body">{{ $evt->description }}</div>
                                        <div class="timeline-meta">{{ $evt->user->name ?? '' }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB: Contratos --}}
        <div class="tab-content" id="tab-contracts">
            {{-- Generate from Template --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-body" style="padding:1rem;">
                    <h4 style="font-size:0.85rem; font-weight:600; margin-bottom:0.75rem;">Generar Contrato desde Plantilla</h4>
                    @if($contractTemplates->isEmpty())
                        <p style="font-size:0.85rem; color:var(--text-muted);">No hay plantillas disponibles. <a href="{{ route('admin.contract-templates.create') }}" style="color:var(--primary);">Crear plantilla</a></p>
                    @else
                        <form method="POST" action="{{ route('rentals.contracts.generate', $rental->id) }}">
                            @csrf
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Plantilla</label>
                                    <select name="contract_template_id" class="form-select" required>
                                        @foreach($contractTemplates as $tpl)
                                            <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->type_label }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Titulo del Contrato</label>
                                    <input type="text" name="title" class="form-input" required placeholder="Ej: Contrato de Arrendamiento - {{ $rental->property->title ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Notas</label>
                                    <input type="text" name="notes" class="form-input" placeholder="Opcional">
                                </div>
                                <div class="form-group" style="display:flex; align-items:flex-end;">
                                    <button type="submit" class="btn btn-primary" style="width:100%;">Generar</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Upload External Contract --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-body" style="padding:1rem;">
                    <h4 style="font-size:0.85rem; font-weight:600; margin-bottom:0.75rem;">Subir Contrato Externo</h4>
                    <form method="POST" action="{{ route('rentals.contracts.upload', $rental->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Titulo</label>
                                <input type="text" name="title" class="form-input" required placeholder="Nombre del contrato">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tipo</label>
                                <select name="type" class="form-select" required>
                                    @foreach(\App\Models\ContractTemplate::TYPES as $tk => $tl)
                                        <option value="{{ $tk }}">{{ $tl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Archivo</label>
                                <input type="file" name="file" class="form-input" required accept=".pdf,.doc,.docx">
                            </div>
                            <div class="form-group" style="display:flex; align-items:flex-end;">
                                <button type="submit" class="btn btn-outline" style="width:100%;">Subir</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Contracts List --}}
            @if($rental->contracts->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p style="font-size:2rem; margin-bottom:0.5rem;">&#128196;</p>
                    <p>No hay contratos en este proceso.</p>
                </div>
            @else
                @foreach($rental->contracts->sortByDesc('created_at') as $contract)
                <div class="card" style="margin-bottom:0.75rem;">
                    <div class="card-body" style="padding:1rem;">
                        <div style="display:flex; align-items:flex-start; gap:0.75rem;">
                            <div style="font-size:1.5rem; flex-shrink:0;">
                                @if($contract->is_signed) &#9989;
                                @elseif($contract->signature_status === 'pending_signature') &#9997;
                                @else &#128196;
                                @endif
                            </div>
                            <div style="flex:1; overflow:hidden;">
                                <div style="font-weight:600; font-size:0.9rem;">{{ $contract->title }}</div>
                                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">
                                    {{ \App\Models\ContractTemplate::TYPES[$contract->type] ?? ucfirst($contract->type) }}
                                    &middot; {{ \App\Models\Contract::SOURCES[$contract->source] ?? $contract->source }}
                                    @if($contract->template) &middot; Plantilla: {{ $contract->template->name }} @endif
                                    &middot; {{ $contract->created_at->format('d/m/Y H:i') }}
                                </div>
                                @if($contract->notes)
                                <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.35rem;">{{ $contract->notes }}</div>
                                @endif
                                @if($contract->is_signed)
                                <div style="font-size:0.78rem; margin-top:0.35rem; padding:0.3rem 0.5rem; background:rgba(16,185,129,0.08); border-radius:4px; display:inline-block;">
                                    Firmado por: {{ $contract->signature_data['signer_name'] ?? '' }}
                                    ({{ $contract->signature_data['signer_email'] ?? '' }})
                                    &middot; {{ $contract->signed_at->format('d/m/Y H:i') }}
                                    &middot; IP: {{ $contract->signature_data['ip'] ?? '' }}
                                </div>
                                @endif
                            </div>
                            <div>
                                <span class="badge badge-{{ match($contract->signature_status) { 'signed' => 'green', 'pending_signature' => 'yellow', default => 'blue' } }}">
                                    {{ $contract->signature_status_label }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div style="display:flex; gap:0.5rem; margin-top:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border); flex-wrap:wrap;">
                            @if($contract->generated_html)
                            <a href="{{ route('contracts.preview', $contract->id) }}" class="btn btn-sm btn-outline" target="_blank">Vista Previa</a>
                            @endif
                            @if($contract->pdf_path)
                            <a href="{{ route('contracts.download', $contract->id) }}" class="btn btn-sm btn-outline">&#8615; Descargar PDF</a>
                            @endif

                            @if(!$contract->is_signed)
                                @if($contract->signature_status !== 'pending_signature')
                                <form method="POST" action="{{ route('contracts.send-signature', $contract->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline">&#9997; Enviar a Firma</button>
                                </form>
                                @endif

                                {{-- Quick Sign --}}
                                <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('signForm-{{ $contract->id }}').style.display = document.getElementById('signForm-{{ $contract->id }}').style.display === 'none' ? 'flex' : 'none';">
                                    Firmar Ahora
                                </button>
                            @endif

                            <form method="POST" action="{{ route('contracts.destroy', $contract->id) }}" style="display:inline; margin-left:auto;" onsubmit="return confirm('Eliminar este contrato?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </div>

                        {{-- Sign Form (hidden) --}}
                        @if(!$contract->is_signed)
                        <div id="signForm-{{ $contract->id }}" style="display:none; gap:0.5rem; align-items:flex-end; margin-top:0.75rem; padding-top:0.75rem; border-top:1px solid var(--border);">
                            <form method="POST" action="{{ route('contracts.sign', $contract->id) }}" style="display:flex; gap:0.5rem; width:100%; align-items:flex-end;">
                                @csrf
                                <div class="form-group" style="flex:1; margin:0;">
                                    <label class="form-label" style="font-size:0.75rem;">Nombre del firmante</label>
                                    <input type="text" name="signer_name" class="form-input" required placeholder="Nombre completo">
                                </div>
                                <div class="form-group" style="flex:1; margin:0;">
                                    <label class="form-label" style="font-size:0.75rem;">Email del firmante</label>
                                    <input type="email" name="signer_email" class="form-input" required placeholder="email@ejemplo.com">
                                </div>
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Al firmar, se registra su IP y fecha/hora como confirmacion digital. Continuar?')">Confirmar Firma</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- TAB: Tasks --}}
        <div class="tab-content" id="tab-tasks">
            @if($rental->tasks->isEmpty())
                <div style="text-align:center; padding:2rem; color:var(--text-muted);">
                    <p>Sin tareas asignadas a este proceso.</p>
                    <a href="{{ route('tasks.create') }}?rental_process_id={{ $rental->id }}" class="btn btn-primary" style="margin-top:0.5rem;">+ Nueva Tarea</a>
                </div>
            @else
                <div style="margin-bottom:0.75rem;">
                    <a href="{{ route('tasks.create') }}?rental_process_id={{ $rental->id }}" class="btn btn-sm btn-outline">+ Nueva Tarea</a>
                </div>
                @foreach($rental->tasks->sortByDesc('created_at') as $task)
                <div class="card" style="margin-bottom:0.5rem;">
                    <div class="card-body" style="padding:0.75rem 1rem; display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.1rem;">
                            @if($task->status === 'completed') &#9745;
                            @elseif($task->status === 'in_progress') &#9193;
                            @else &#9744;
                            @endif
                        </span>
                        <div style="flex:1;">
                            <div style="font-size:0.88rem; font-weight:500; {{ $task->status === 'completed' ? 'text-decoration:line-through; opacity:0.6;' : '' }}">
                                {{ $task->title }}
                            </div>
                            <div style="font-size:0.72rem; color:var(--text-muted);">
                                {{ $task->user->name ?? '' }}
                                @if($task->due_date)
                                    &middot; Vence: {{ $task->due_date->format('d/m/Y') }}
                                    @if($task->status !== 'completed' && $task->due_date->isPast())
                                        <span class="badge badge-red">Vencida</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <span class="badge badge-{{ match($task->status) { 'completed' => 'green', 'in_progress' => 'blue', 'cancelled' => 'red', default => 'yellow' } }}">
                            {{ match($task->status) { 'completed' => 'Completada', 'in_progress' => 'En progreso', 'cancelled' => 'Cancelada', default => 'Pendiente' } }}
                        </span>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>
@endsection
