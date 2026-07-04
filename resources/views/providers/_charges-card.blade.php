{{--
    Tarjeta "Proveedores del proceso" — reutilizada en operations/show.blade.php
    y admin/rentas/gestion/show.blade.php. Requiere:
    - $charges: colección de ProviderCharge (ya cargada, con providerCompany/providerContact)
    - $providerCompanies: colección de ProviderCompany activas con sus contacts cargados
    - $storeRoute: URL del form de alta (distinta según Operation o RentalProcess)
--}}
<div class="card" style="margin-bottom:0.75rem;">
    <div class="card-body" style="padding:0.85rem;">
        <div class="info-label" style="margin-bottom:0.5rem;">Proveedores del proceso</div>

        @forelse($charges as $charge)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0.4rem 0; border-bottom:1px solid var(--border); font-size:0.8rem;">
            <div>
                <div style="font-weight:600;">{{ $charge->providerCompany->name }}{{ $charge->providerContact ? ' — ' . $charge->providerContact->name : '' }}</div>
                <div style="color:var(--text-muted); font-size:0.72rem;">{{ $charge->service_description }} &middot; {{ $charge->flow_label }}</div>
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem;">
                <span>${{ number_format($charge->amount ?? $charge->calculateCommission(), 0) }}</span>
                <span class="badge badge-{{ $charge->status_color }}" style="font-size:0.68rem;">{{ $charge->status_label }}</span>
                @if($charge->status !== 'liquidado')
                <form method="POST" action="{{ route('provider-charges.update-status', $charge->id) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $charge->status === 'registrado' ? 'confirmado' : 'liquidado' }}">
                    <button type="submit" class="btn btn-sm btn-outline" style="font-size:0.68rem; padding:2px 8px;">
                        {{ $charge->status === 'registrado' ? 'Confirmar' : ($charge->flow === 'cargo' ? 'Marcar pagado' : 'Marcar cobrado') }}
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('provider-charges.destroy', $charge->id) }}" onsubmit="return confirm('¿Eliminar este registro?');">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.9rem;">&times;</button>
                </form>
            </div>
        </div>
        @empty
        <p style="font-size:0.78rem; color:var(--text-muted);">Sin proveedores registrados en este proceso todavía.</p>
        @endforelse

        <form method="POST" action="{{ $storeRoute }}" style="display:flex; gap:0.4rem; margin-top:0.6rem; flex-wrap:wrap;" id="provider-charge-form-{{ Str::random(6) }}">
            @csrf
            @php $formId = 'pc_' . uniqid(); @endphp
            <select name="provider_company_id" class="form-select provider-company-select" data-form="{{ $formId }}" style="font-size:0.78rem; flex:1; min-width:130px;" required>
                <option value="">-- Empresa --</option>
                @foreach($providerCompanies as $pc)
                <option value="{{ $pc->id }}">{{ $pc->name }}</option>
                @endforeach
            </select>
            <select name="provider_contact_id" class="form-select provider-contact-select" data-form="{{ $formId }}" style="font-size:0.78rem; flex:1; min-width:110px;">
                <option value="">-- Contacto (opcional) --</option>
            </select>
            <select name="flow" class="form-select" style="font-size:0.78rem; width:110px;">
                @foreach(\App\Models\ProviderCharge::FLOWS as $val => $label)
                <option value="{{ $val }}">{{ $val === 'cargo' ? 'Cargo' : 'Comisión' }}</option>
                @endforeach
            </select>
            <input type="text" name="service_description" class="form-input" placeholder="Servicio (ej. Limpieza)" required style="font-size:0.78rem; flex:1; min-width:120px;">
            <input type="number" name="amount" class="form-input" placeholder="Monto fijo" step="0.01" min="0" style="font-size:0.78rem; width:100px;">
            <input type="number" name="commission_percentage" class="form-input" placeholder="% comisión" step="0.01" min="0" max="100" style="font-size:0.78rem; width:100px;">
            <button type="submit" class="btn btn-sm btn-outline">Agregar</button>
        </form>
        @if($providerCompanies->isEmpty())
        <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.4rem;">No hay proveedores activos registrados — <a href="{{ route('providers.create') }}" target="_blank">crear uno →</a></p>
        @endif
    </div>
</div>

<script>
window.__providerContactsData = window.__providerContactsData || {};
Object.assign(window.__providerContactsData, {!! $providerCompanies->mapWithKeys(fn ($pc) => [
    $pc->id => $pc->contacts->where('status', 'active')->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'role' => $c->role])->values(),
])->toJson() !!});
</script>

@once
<script>
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('provider-company-select')) return;
    var formId = e.target.dataset.form;
    var companyId = e.target.value;
    var contactSelect = document.querySelector('.provider-contact-select[data-form="' + formId + '"]');
    contactSelect.innerHTML = '<option value="">-- Contacto (opcional) --</option>';
    var contacts = (window.__providerContactsData || {})[companyId] || [];
    contacts.forEach(function (c) {
        var opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name + (c.role ? ' (' + c.role + ')' : '');
        contactSelect.appendChild(opt);
    });
});
</script>
@endonce
