@extends('layouts.app-sidebar')
@section('title', $campaign ? 'Editar Campana' : 'Nueva Campana')

@section('styles')
<style>
.camp-form-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    max-width: 720px; overflow: hidden;
}
.camp-form-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
}
.camp-form-header h3 { font-size: 1rem; font-weight: 600; }
.camp-form-body { padding: 1.5rem; }

.section-label {
    font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.5px; margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.section-label:first-child { margin-top: 0; }

/* Status cards */
.status-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1rem; }
.status-card {
    padding: 0.6rem; border-radius: var(--radius); border: 2px solid var(--border);
    text-align: center; cursor: pointer; transition: all 0.15s; position: relative;
}
.status-card:hover { border-color: var(--primary); }
.status-card.active-green { border-color: #10b981; background: #ecfdf5; }
.status-card.active-yellow { border-color: #f59e0b; background: #fffbeb; }
.status-card.active-blue { border-color: #3b82f6; background: #eff6ff; }
.status-card input { position: absolute; opacity: 0; pointer-events: none; }
.status-card-icon { font-size: 1.1rem; margin-bottom: 0.1rem; }
.status-card-label { font-size: 0.78rem; font-weight: 500; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.marketing.dashboard') }}" style="font-size:0.82rem; color:var(--text-muted);">Marketing</a>
    <span style="color:var(--text-muted); font-size:0.75rem;">/</span>
    <a href="{{ route('admin.marketing.campaigns') }}" style="font-size:0.82rem; color:var(--text-muted);">Campanas</a>
    <span style="color:var(--text-muted); font-size:0.75rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $campaign ? 'Editar' : 'Nueva' }}</span>
</div>

<div class="camp-form-card">
    <div class="camp-form-header">
        <h3>{{ $campaign ? 'Editar Campana' : 'Nueva Campana' }}</h3>
        @if($campaign)
            @if($campaign->status === 'active')
                <span class="badge badge-green">Activa</span>
            @elseif($campaign->status === 'paused')
                <span class="badge badge-yellow">Pausada</span>
            @else
                <span class="badge badge-blue">Completada</span>
            @endif
        @endif
    </div>
    <div class="camp-form-body">
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ $campaign ? route('admin.marketing.campaigns.update', $campaign) : route('admin.marketing.campaigns.store') }}">
            @csrf
            @if($campaign) @method('PUT') @endif

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Canal <span class="required">*</span></label>
                    <select name="marketing_channel_id" class="form-select" required>
                        <option value="">Seleccionar canal</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}" {{ old('marketing_channel_id', $campaign->marketing_channel_id ?? '') == $channel->id ? 'selected' : '' }}>
                                {{ $channel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $campaign->name ?? '') }}" required placeholder="Primavera 2026 - Google">
                </div>
            </div>

            <div class="section-label">Estado</div>
            <div class="status-cards">
                @php $curStatus = old('status', $campaign->status ?? 'active'); @endphp
                <label class="status-card {{ $curStatus === 'active' ? 'active-green' : '' }}"
                       onclick="document.querySelectorAll('.status-card').forEach(c=>c.className='status-card'); this.classList.add('status-card','active-green');">
                    <input type="radio" name="status" value="active" {{ $curStatus === 'active' ? 'checked' : '' }}>
                    <div class="status-card-icon">&#9654;</div>
                    <div class="status-card-label">Activa</div>
                </label>
                <label class="status-card {{ $curStatus === 'paused' ? 'active-yellow' : '' }}"
                       onclick="document.querySelectorAll('.status-card').forEach(c=>c.className='status-card'); this.classList.add('status-card','active-yellow');">
                    <input type="radio" name="status" value="paused" {{ $curStatus === 'paused' ? 'checked' : '' }}>
                    <div class="status-card-icon">&#10074;&#10074;</div>
                    <div class="status-card-label">Pausada</div>
                </label>
                <label class="status-card {{ $curStatus === 'completed' ? 'active-blue' : '' }}"
                       onclick="document.querySelectorAll('.status-card').forEach(c=>c.className='status-card'); this.classList.add('status-card','active-blue');">
                    <input type="radio" name="status" value="completed" {{ $curStatus === 'completed' ? 'checked' : '' }}>
                    <div class="status-card-icon">&#10003;</div>
                    <div class="status-card-label">Completada</div>
                </label>
            </div>

            <div class="section-label">Presupuesto</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Presupuesto <span class="required">*</span></label>
                    <input type="number" name="budget" class="form-input" value="{{ old('budget', $campaign->budget ?? '0') }}" min="0" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Gastado</label>
                    <input type="number" name="spent" class="form-input" value="{{ old('spent', $campaign->spent ?? '0') }}" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Moneda <span class="required">*</span></label>
                    <select name="currency" class="form-select">
                        <option value="MXN" {{ old('currency', $campaign->currency ?? 'MXN') === 'MXN' ? 'selected' : '' }}>MXN</option>
                        <option value="USD" {{ old('currency', $campaign->currency ?? '') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>

            <div class="section-label">Periodo</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="start_date" class="form-input" value="{{ old('start_date', $campaign?->start_date?->format('Y-m-d') ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="end_date" class="form-input" value="{{ old('end_date', $campaign?->end_date?->format('Y-m-d') ?? '') }}">
                </div>
            </div>

            <div class="section-label">Notas</div>
            <div class="form-group">
                <textarea name="notes" class="form-textarea" rows="3" placeholder="Notas sobre la campana...">{{ old('notes', $campaign->notes ?? '') }}</textarea>
            </div>

            <div class="form-actions">
                @if($campaign)
                    <form method="POST" action="{{ route('admin.marketing.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Eliminar esta campana?')" style="margin-right:auto;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                @endif
                <a href="{{ route('admin.marketing.campaigns') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">{{ $campaign ? 'Actualizar' : 'Crear Campana' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
