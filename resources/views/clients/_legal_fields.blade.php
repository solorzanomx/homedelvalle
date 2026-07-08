{{--
    Partial: Datos Legales del Cliente
    Variables esperadas: $client (para edit) o null (para create)
    Incluir con: @include('clients._legal_fields', ['client' => $client ?? null])
--}}
@php
    $c       = $client ?? null;
    $estados = [
        'Aguascalientes','Baja California','Baja California Sur','Campeche',
        'Chiapas','Chihuahua','Ciudad de México','Coahuila','Colima','Durango',
        'Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco',
        'Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla',
        'Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora',
        'Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas',
        'Extranjero',
    ];
    $completeness = $c ? $c->legal_completeness : 0;
@endphp

{{-- Encabezado de sección con indicador de completitud --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin:1.5rem 0 .75rem;padding-bottom:.5rem;border-bottom:1px solid var(--border);">
    <span style="font-size:.9rem;font-weight:600;color:var(--text);">Datos Legales</span>
    @if($c)
    <span id="legal-completeness-badge" style="font-size:.75rem;font-weight:700;padding:2px 10px;border-radius:20px;
        background:{{ $completeness >= 80 ? '#dcfce7' : ($completeness >= 40 ? '#fef9c3' : '#fee2e2') }};
        color:{{ $completeness >= 80 ? '#166534' : ($completeness >= 40 ? '#92400e' : '#991b1b') }};">
        {{ $completeness }}% completo
    </span>
    @endif
</div>

{{-- Bloque de identidad — aplica a quien representa a un inmueble propio (vendedor o propietario en renta) --}}
<div class="type-section" data-types="venta,renta_propietario">

{{-- ── Nombre desglosado ── --}}
<div class="section-title" style="margin-top:0;font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">Nombre para contratos</div>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">Nombre(s)</label>
        <input type="text" name="first_name" class="form-input legal-field" value="{{ old('first_name', $c?->first_name) }}" placeholder="María Fernanda">
    </div>
    <div class="form-group">
        <label class="form-label">Apellido Paterno</label>
        <input type="text" name="last_name_paterno" class="form-input legal-field" value="{{ old('last_name_paterno', $c?->last_name_paterno) }}" placeholder="González">
    </div>
    <div class="form-group">
        <label class="form-label">Apellido Materno</label>
        <input type="text" name="last_name_materno" class="form-input legal-field" value="{{ old('last_name_materno', $c?->last_name_materno) }}" placeholder="Ríos">
    </div>
    <div class="form-group">
        <label class="form-label" style="font-size:.75rem;color:var(--text-muted);">Nombre en contratos</label>
        <div id="legal-name-preview" style="padding:.45rem .75rem;background:var(--bg);border-radius:var(--radius);font-size:.82rem;font-weight:600;color:#0E304B;letter-spacing:.3px;min-height:34px;">
            {{ $c?->full_name_legal ?: '—' }}
        </div>
    </div>
</div>

{{-- ── Datos personales ── --}}
<div class="section-title" style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">Datos personales</div>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">Fecha de Nacimiento</label>
        <input type="date" name="birth_date" class="form-input legal-field" value="{{ old('birth_date', $c?->birth_date?->format('Y-m-d')) }}">
    </div>
    <div class="form-group">
        <label class="form-label">Estado de Nacimiento</label>
        <select name="birth_state" class="form-select legal-field">
            <option value="">Seleccionar</option>
            @foreach($estados as $estado)
                <option value="{{ $estado }}" {{ old('birth_state', $c?->birth_state) === $estado ? 'selected' : '' }}>{{ $estado }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Sexo (CURP)</label>
        <div style="display:flex;gap:.75rem;padding-top:.3rem;">
            @foreach(['H' => 'Hombre', 'M' => 'Mujer'] as $val => $lbl)
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;cursor:pointer;">
                <input type="radio" name="gender" value="{{ $val }}" {{ old('gender', $c?->gender) === $val ? 'checked' : '' }}>
                {{ $lbl }}
            </label>
            @endforeach
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Nacionalidad</label>
        <input type="text" name="nationality" class="form-input" value="{{ old('nationality', $c?->nationality ?? 'mexicana') }}" placeholder="mexicana">
    </div>
    <div class="form-group">
        <label class="form-label">Estado Civil</label>
        <select name="marital_status" class="form-select legal-field" id="marital_status_sel" onchange="toggleSpouseFields(this.value)">
            <option value="">Seleccionar</option>
            <option value="soltero"     {{ old('marital_status', $c?->marital_status) === 'soltero'     ? 'selected' : '' }}>Soltero/a</option>
            <option value="casado"      {{ old('marital_status', $c?->marital_status) === 'casado'      ? 'selected' : '' }}>Casado/a</option>
            <option value="divorciado"  {{ old('marital_status', $c?->marital_status) === 'divorciado'  ? 'selected' : '' }}>Divorciado/a</option>
            <option value="viudo"       {{ old('marital_status', $c?->marital_status) === 'viudo'       ? 'selected' : '' }}>Viudo/a</option>
            <option value="union_libre" {{ old('marital_status', $c?->marital_status) === 'union_libre' ? 'selected' : '' }}>Unión Libre</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Ocupación / Profesión</label>
        <input type="text" name="occupation" class="form-input" value="{{ old('occupation', $c?->occupation) }}" placeholder="Ingeniero, Comerciante...">
    </div>
</div>

{{-- ── CURP y RFC ── --}}
<div class="section-title" style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">CURP y RFC</div>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">
            CURP
            @if($c?->curp_verified_at)
            <span style="font-size:.7rem;background:#dcfce7;color:#166534;border-radius:4px;padding:1px 6px;font-weight:700;margin-left:4px;">✓ Verificado</span>
            @endif
        </label>
        <div style="display:flex;gap:.4rem;align-items:center;">
            <input type="text" name="curp" id="curp_input" class="form-input legal-field" value="{{ old('curp', $c?->curp) }}"
                   placeholder="XXXX000000XXXXXXXX" maxlength="18" style="text-transform:uppercase;flex:1;"
                   oninput="validateCurp(this)">
            <span id="curp_status" style="font-size:1.1rem;width:22px;flex-shrink:0;"></span>
        </div>
        <p id="curp_hint" class="form-hint" style="display:none;"></p>
        @if($c?->curp && !$c->curp_verified_at)
        <p class="form-hint" style="margin-top:.3rem;">
            <a href="https://www.gob.mx/curp/" target="_blank" style="color:var(--primary);">Verificar en RENAPO →</a>
        </p>
        @endif
    </div>
    <div class="form-group">
        <label class="form-label">
            RFC
            @if($c?->rfc_verified_at)
            <span style="font-size:.7rem;background:#dcfce7;color:#166534;border-radius:4px;padding:1px 6px;font-weight:700;margin-left:4px;">✓ Verificado</span>
            @endif
        </label>
        <div style="display:flex;gap:.4rem;align-items:center;">
            <input type="text" name="rfc" id="rfc_input" class="form-input legal-field" value="{{ old('rfc', $c?->rfc) }}"
                   placeholder="XXXX000000XXX" maxlength="13" style="text-transform:uppercase;flex:1;"
                   oninput="validateRfc(this)">
            <span id="rfc_status" style="font-size:1.1rem;width:22px;flex-shrink:0;"></span>
        </div>
        <p id="rfc_hint" class="form-hint" style="display:none;"></p>
        @if($c?->rfc && !$c->rfc_verified_at)
        <p class="form-hint" style="margin-top:.3rem;">
            <a href="https://www.sat.gob.mx/aplicacion/operacion/74788/consulta-tu-clave-del-rfc" target="_blank" style="color:var(--primary);">Verificar en SAT →</a>
        </p>
        @endif
    </div>
</div>

{{-- ── Identificación oficial ── --}}
<div class="section-title" style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">Identificación oficial</div>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">Tipo de ID</label>
        <select name="id_type" class="form-select legal-field">
            <option value="">Seleccionar</option>
            <option value="INE"              {{ old('id_type', $c?->id_type) === 'INE'              ? 'selected' : '' }}>INE / IFE</option>
            <option value="pasaporte"        {{ old('id_type', $c?->id_type) === 'pasaporte'        ? 'selected' : '' }}>Pasaporte</option>
            <option value="cedula_profesional" {{ old('id_type', $c?->id_type) === 'cedula_profesional' ? 'selected' : '' }}>Cédula Profesional</option>
            <option value="otro"             {{ old('id_type', $c?->id_type) === 'otro'             ? 'selected' : '' }}>Otro</option>
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Número de Identificación</label>
        <input type="text" name="id_number" class="form-input legal-field" value="{{ old('id_number', $c?->id_number) }}" placeholder="Folio o número">
    </div>
    <div class="form-group">
        <label class="form-label">Vigencia</label>
        <input type="date" name="id_expiry" class="form-input" value="{{ old('id_expiry', $c?->id_expiry?->format('Y-m-d')) }}">
        @if($c?->id_expiry && $c->id_expiry->isPast())
        <p class="form-hint" style="color:var(--danger);">⚠ Identificación vencida</p>
        @endif
    </div>
</div>

{{-- ── Domicilio estructurado ── --}}
<div class="section-title" style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">Domicilio para contratos</div>
<div class="form-grid">
    <div class="form-group full-width">
        <label class="form-label">Calle y Número</label>
        <input type="text" name="address_street" class="form-input legal-field" value="{{ old('address_street', $c?->address_street) }}" placeholder="Av. Insurgentes Sur 1234, Int. 5">
    </div>
    <div class="form-group">
        <label class="form-label">Colonia</label>
        <input type="text" name="address_colony" class="form-input legal-field" value="{{ old('address_colony', $c?->address_colony) }}" placeholder="Del Valle">
    </div>
    <div class="form-group">
        <label class="form-label">Alcaldía / Municipio</label>
        <input type="text" name="address_municipality" class="form-input legal-field" value="{{ old('address_municipality', $c?->address_municipality) }}" placeholder="Benito Juárez">
    </div>
    <div class="form-group">
        <label class="form-label">Estado</label>
        <select name="address_state" class="form-select legal-field">
            <option value="">Seleccionar</option>
            @foreach($estados as $estado)
                <option value="{{ $estado }}" {{ old('address_state', $c?->address_state) === $estado ? 'selected' : '' }}>{{ $estado }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label class="form-label">Código Postal</label>
        <input type="text" name="address_zip" class="form-input legal-field" value="{{ old('address_zip', $c?->address_zip) }}" maxlength="5" placeholder="03100">
    </div>
</div>

{{-- ── Datos de renta / cónyuge ── --}}
<div id="spouse-section" style="display:{{ in_array(old('marital_status', $c?->marital_status), ['casado','union_libre']) ? 'block' : 'none' }};">
    <div class="section-title" style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">Régimen y cónyuge</div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Régimen Patrimonial</label>
            <select name="marital_regime" class="form-select">
                <option value="">Seleccionar</option>
                <option value="separacion_bienes" {{ old('marital_regime', $c?->marital_regime) === 'separacion_bienes' ? 'selected' : '' }}>Separación de Bienes</option>
                <option value="sociedad_conyugal"  {{ old('marital_regime', $c?->marital_regime) === 'sociedad_conyugal'  ? 'selected' : '' }}>Sociedad Conyugal</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Nombre del Cónyuge</label>
            <input type="text" name="spouse_name" class="form-input" value="{{ old('spouse_name', $c?->spouse_name) }}" placeholder="Nombre completo">
        </div>
        <div class="form-group">
            <label class="form-label">CURP del Cónyuge</label>
            <input type="text" name="spouse_curp" class="form-input" value="{{ old('spouse_curp', $c?->spouse_curp) }}" maxlength="18" style="text-transform:uppercase;" placeholder="XXXX000000XXXXXXXX">
        </div>
    </div>
</div>

</div>
{{-- /type-section venta,renta_propietario --}}

{{-- ── Datos bancarios (propietario renta) — solo aplica a quien recibe depósitos de renta ── --}}
<div class="type-section" data-types="renta_propietario">
<div class="section-title" style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:none;padding-bottom:0;margin-bottom:.6rem;">Datos bancarios <span style="font-weight:400;font-style:italic;">(para depósito de rentas)</span></div>
<div class="form-grid">
    <div class="form-group">
        <label class="form-label">CLABE Interbancaria</label>
        <input type="text" name="bank_clabe" class="form-input" value="{{ old('bank_clabe', $c?->bank_clabe) }}" maxlength="18" placeholder="18 dígitos" id="clabe_input" oninput="validateClabe(this)">
        <p id="clabe_hint" class="form-hint" style="display:none;"></p>
    </div>
    <div class="form-group">
        <label class="form-label">Banco</label>
        <input type="text" name="bank_name" class="form-input" value="{{ old('bank_name', $c?->bank_name) }}" placeholder="BBVA, Banorte, HSBC...">
    </div>
</div>
</div>
{{-- /type-section renta_propietario --}}

<script>
// ── CURP Validator ──────────────────────────────────────────
function validateCurp(input) {
    var val = input.value.toUpperCase().replace(/\s/g,'');
    input.value = val;
    var status = document.getElementById('curp_status');
    var hint   = document.getElementById('curp_hint');
    if (!val) { status.textContent=''; hint.style.display='none'; return; }

    var re = /^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[HM]{1}(AS|BC|BS|CC|CH|CL|CM|CS|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/;
    if (val.length < 18) {
        status.textContent = '…'; hint.style.display='none';
    } else if (re.test(val)) {
        status.textContent = '✅';
        hint.style.display = 'none';
        // Auto-fill fecha nacimiento y sexo si están vacíos
        tryFillFromCurp(val);
    } else {
        status.textContent = '❌';
        hint.style.display = 'block';
        hint.style.color = 'var(--danger)';
        hint.textContent = 'Formato de CURP inválido';
    }
    updateLegalCompleteness();
}

function tryFillFromCurp(curp) {
    // CURP: posición 5-10 = YYMMDD fecha, pos 11 = sexo H/M
    var yy = curp.substring(4,6);
    var mm = curp.substring(6,8);
    var dd = curp.substring(8,10);
    var sex = curp.substring(10,11);
    var yyyy = parseInt(yy) > parseInt(new Date().getFullYear().toString().substring(2)) ? '19'+yy : '20'+yy;
    var bdField = document.querySelector('input[name="birth_date"]');
    if (bdField && !bdField.value) bdField.value = yyyy+'-'+mm+'-'+dd;
    var sexRadio = document.querySelector('input[name="gender"][value="'+sex+'"]');
    if (sexRadio && !document.querySelector('input[name="gender"]:checked')) sexRadio.checked = true;
}

// ── RFC Validator ────────────────────────────────────────────
function validateRfc(input) {
    var val = input.value.toUpperCase().replace(/\s/g,'');
    input.value = val;
    var status = document.getElementById('rfc_status');
    var hint   = document.getElementById('rfc_hint');
    if (!val) { status.textContent=''; hint.style.display='none'; return; }

    // RFC persona física: 4 letras + 6 dígitos + 3 caracteres homoclave
    var re = /^[A-Z&Ñ]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{3}$/;
    if (val.length < 12) {
        status.textContent = '…'; hint.style.display='none';
    } else if (re.test(val)) {
        status.textContent = '✅';
        hint.style.display = 'none';
    } else {
        status.textContent = '❌';
        hint.style.display = 'block';
        hint.style.color = 'var(--danger)';
        hint.textContent = 'Formato de RFC inválido';
    }
    updateLegalCompleteness();
}

// ── CLABE Validator ───────────────────────────────────────────
function validateClabe(input) {
    var val = input.value.replace(/\D/g,'');
    input.value = val;
    var hint = document.getElementById('clabe_hint');
    if (!val) { hint.style.display='none'; return; }
    if (val.length === 18) {
        // Verificar dígito control CLABE (algoritmo oficial)
        var w=[3,7,1,3,7,1,3,7,1,3,7,1,3,7,1,3,7];
        var sum=0;
        for(var i=0;i<17;i++) sum += parseInt(val[i])*w[i];
        var ctrl = (10-(sum%10))%10;
        if(ctrl===parseInt(val[17])){
            hint.style.display='block'; hint.style.color='#166534';
            hint.textContent='✓ CLABE válida';
        } else {
            hint.style.display='block'; hint.style.color='var(--danger)';
            hint.textContent='Dígito de control incorrecto — revisa la CLABE';
        }
    } else {
        hint.style.display='none';
    }
}

// ── Cónyuge toggle ────────────────────────────────────────────
function toggleSpouseFields(val) {
    var show = val === 'casado' || val === 'union_libre';
    document.getElementById('spouse-section').style.display = show ? 'block' : 'none';
}

// ── Progressive disclosure por tipo de cliente ─────────────────
// Muestra/oculta cualquier .type-section[data-types] de la página (de este
// partial o de quien lo incluya, ej. create.blade.php) según los checkboxes
// interest_types[] marcados. Sin nada marcado, se muestra todo (default
// conservador — nunca ocultar antes de que el broker indique una intención).
function updateVisibleSections() {
    var checked = Array.from(document.querySelectorAll('input[name="interest_types[]"]:checked')).map(function(cb) { return cb.value; });
    document.querySelectorAll('.type-section[data-types]').forEach(function(el) {
        var types = el.dataset.types.split(',');
        var show = checked.length === 0 || types.some(function(t) { return checked.includes(t); });
        el.style.display = show ? '' : 'none';
    });
}

// ── Preview nombre legal ──────────────────────────────────────
function updateLegalNamePreview() {
    var fn  = (document.querySelector('input[name="first_name"]')?.value||'').trim().toUpperCase();
    var lp  = (document.querySelector('input[name="last_name_paterno"]')?.value||'').trim().toUpperCase();
    var lm  = (document.querySelector('input[name="last_name_materno"]')?.value||'').trim().toUpperCase();
    var preview = document.getElementById('legal-name-preview');
    if (preview) preview.textContent = [lp,lm,fn].filter(Boolean).join(' ') || '—';
    updateLegalCompleteness();
}

// ── Completeness tracker ──────────────────────────────────────
var legalFields = ['first_name','last_name_paterno','last_name_materno','birth_date',
    'birth_state','curp','rfc','id_type','id_number','address_street',
    'address_colony','address_municipality','address_state','address_zip'];
var legalGenderFilled = false;

function updateLegalCompleteness() {
    var badge = document.getElementById('legal-completeness-badge');
    if (!badge) return;
    var total = legalFields.length + 1; // +1 for gender
    var filled = 0;
    legalFields.forEach(function(f){
        var el = document.querySelector('[name="'+f+'"]');
        if (el && el.value.trim()) filled++;
    });
    if (document.querySelector('input[name="gender"]:checked')) filled++;
    var pct = Math.round(filled/total*100);
    badge.textContent = pct + '% completo';
    badge.style.background = pct>=80?'#dcfce7':(pct>=40?'#fef9c3':'#fee2e2');
    badge.style.color = pct>=80?'#166534':(pct>=40?'#92400e':'#991b1b');
}

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function(){
    // Attach name preview listeners
    ['first_name','last_name_paterno','last_name_materno'].forEach(function(f){
        var el = document.querySelector('input[name="'+f+'"]');
        if(el) el.addEventListener('input', updateLegalNamePreview);
    });
    // Legal fields completeness
    document.querySelectorAll('.legal-field').forEach(function(el){
        el.addEventListener('change', updateLegalCompleteness);
        el.addEventListener('input', updateLegalCompleteness);
    });
    // Validate existing values on load
    var curpEl = document.getElementById('curp_input');
    if (curpEl && curpEl.value) validateCurp(curpEl);
    var rfcEl = document.getElementById('rfc_input');
    if (rfcEl && rfcEl.value) validateRfc(rfcEl);
    var clabeEl = document.getElementById('clabe_input');
    if (clabeEl && clabeEl.value) validateClabe(clabeEl);
    var msEl = document.getElementById('marital_status_sel');
    if (msEl && msEl.value) toggleSpouseFields(msEl.value);
    updateLegalCompleteness();

    // Progressive disclosure por tipo de cliente — enlaza el change de los
    // checkboxes interest_types[] (estén donde estén en la página) y aplica
    // el estado inicial correcto tras un error de validación con old().
    document.querySelectorAll('input[name="interest_types[]"]').forEach(function(cb) {
        cb.addEventListener('change', updateVisibleSections);
    });
    updateVisibleSections();
});
</script>
