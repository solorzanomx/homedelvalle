@extends('layouts.portal')
@section('title', 'Mi Expediente')

@section('styles')
/* ── Expediente ────────────────────────────────────────────── */
.exp-hero {
    background: linear-gradient(135deg, #0E304B 0%, #1a4a6e 100%);
    border-radius: var(--radius);
    padding: 1.5rem 1.75rem;
    color: #fff;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
}
.exp-hero-icon { font-size: 2.2rem; flex-shrink: 0; }
.exp-hero-title { font-size: 1.1rem; font-weight: 800; margin-bottom: .25rem; }
.exp-hero-sub { font-size: .82rem; color: rgba(255,255,255,.75); line-height: 1.5; }

.exp-progress-bar {
    height: 8px;
    background: rgba(255,255,255,.25);
    border-radius: 4px;
    overflow: hidden;
    margin-top: .75rem;
}
.exp-progress-fill {
    height: 100%;
    background: #22C55E;
    border-radius: 4px;
    transition: width .5s ease;
}

/* ── Section tabs ─────────────────────────────────────────── */
.exp-tabs {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}
.exp-tab {
    padding: .45rem 1rem;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--card);
    font-size: .8rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .35rem;
    transition: all .14s;
}
.exp-tab.active {
    background: #0E304B;
    color: #fff;
    border-color: #0E304B;
}
.exp-tab .tab-badge {
    font-size: .65rem;
    padding: 1px 5px;
    border-radius: 8px;
    font-weight: 700;
}
.exp-tab.active .tab-badge { background: rgba(255,255,255,.2); color: #fff; }
.exp-tab:not(.active) .tab-badge { background: var(--bg); color: var(--text-muted); }

/* ── Section cards ────────────────────────────────────────── */
.exp-section { display: none; }
.exp-section.active { display: block; }

.section-progress {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem 1rem;
    background: var(--bg);
    border-radius: 8px;
    margin-bottom: 1.25rem;
    font-size: .8rem;
}
.section-progress-bar-bg {
    flex: 1;
    height: 6px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
}
.section-progress-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width .4s;
}

/* ── Doc upload area ─────────────────────────────────────── */
.doc-upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    padding: 1.25rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s;
    font-size: .83rem;
    color: var(--text-muted);
}
.doc-upload-zone:hover { border-color: #0E304B; }
.doc-item {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .55rem .75rem;
    background: var(--bg);
    border-radius: 8px;
    margin-bottom: .4rem;
    font-size: .82rem;
}
.doc-item-icon { font-size: 1.1rem; flex-shrink: 0; }
.doc-item-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Guarantee type cards ─────────────────────────────────── */
.guarantee-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.guarantee-card {
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 1rem .75rem;
    text-align: center;
    font-size: .8rem;
    font-weight: 600;
    color: var(--text-muted);
}
.guarantee-card.active {
    border-color: #0E304B;
    background: #EFF6FF;
    color: #0E304B;
}
.guarantee-card .g-icon { font-size: 1.6rem; margin-bottom: .4rem; }

/* ── Pagare info card ─────────────────────────────────────── */
.pagare-card {
    background: #FFFBEB;
    border: 1px solid #FDE68A;
    border-radius: var(--radius);
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
}
@endsection

@section('content')

@php
    $totalPct = $client->legal_completeness;
    $allSectionPcts = collect($sections)->pluck('pct');
    $avgPct = $allSectionPcts->count() ? round($allSectionPcts->avg()) : 0;

    $estados = ['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua',
        'Ciudad de México','Coahuila','Colima','Durango','Estado de México','Guanajuato','Guerrero',
        'Hidalgo','Jalisco','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla',
        'Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco',
        'Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas','Extranjero'];

    $guaranteeType = $rentalAsInquilino?->guarantee_type;
    $hasAval    = $guaranteeType && in_array($guaranteeType, ['aval','aval_pagares']);
    $hasPagares = $guaranteeType && in_array($guaranteeType, ['pagares','aval_pagares']);
    $hasPoliza  = $guaranteeType === 'poliza_juridica';
@endphp

{{-- Hero con progreso global --}}
<div class="exp-hero">
    <div class="exp-hero-icon">📋</div>
    <div style="flex:1;">
        <div class="exp-hero-title">Mi Expediente Digital</div>
        <div class="exp-hero-sub">Completa tu información para agilizar el proceso al máximo cuando llegue el momento de firmar.</div>
        <div class="exp-progress-bar">
            <div class="exp-progress-fill" style="width:{{ $totalPct }}%;"></div>
        </div>
        <div style="font-size:.75rem;margin-top:.4rem;color:rgba(255,255,255,.8);">
            {{ $totalPct }}% completo
            @if($totalPct >= 80) &nbsp;— ¡Expediente casi listo!
            @elseif($totalPct >= 40) &nbsp;— Buen avance, continúa
            @else &nbsp;— Empecemos
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div style="background:#ECFDF5;border:1px solid #BBF7D0;color:#166534;border-radius:var(--radius);padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">
    ✅ {{ session('success') }}
</div>
@endif

{{-- Tabs de sección --}}
<div class="exp-tabs">
    <button class="exp-tab active" onclick="showSection('datos',this)">
        👤 Datos personales
        <span class="tab-badge">{{ $sections['datos']['pct'] }}%</span>
    </button>
    <button class="exp-tab" onclick="showSection('identificacion',this)">
        🪪 Identificación
        <span class="tab-badge">{{ $sections['identificacion']['pct'] }}%</span>
    </button>
    @if($isArrendador || $isVendedor)
    <button class="exp-tab" onclick="showSection('inmueble',this)">
        🏠 Docs. Inmueble
        <span class="tab-badge">{{ $sections['documentos_inmueble']['pct'] ?? 0 }}%</span>
    </button>
    @endif
    @if($isComprador)
    <button class="exp-tab" onclick="showSection('financiamiento',this)">
        💳 Financiamiento
        <span class="tab-badge">{{ $sections['financiamiento']['pct'] ?? 0 }}%</span>
    </button>
    @endif
    @if($isArrendatario)
    <button class="exp-tab" onclick="showSection('ingresos',this)">
        💰 Ingresos
        <span class="tab-badge">{{ $sections['ingresos']['pct'] ?? 0 }}%</span>
    </button>
    <button class="exp-tab" onclick="showSection('garantia',this)">
        🔒 Garantía
        <span class="tab-badge">{{ $sections['garantia']['pct'] ?? 0 }}%</span>
    </button>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     SECCIÓN 1: DATOS PERSONALES
════════════════════════════════════════════════════════════ --}}
<div class="exp-section active" id="sec-datos">
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header">
            <span style="font-size:.85rem;font-weight:700;">Nombre completo</span>
        </div>
        <div class="card-body">
            @php $s = $sections['datos']; @endphp
            <div class="section-progress">
                <span style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">{{ $s['filled'] }}/{{ $s['total'] }} campos</span>
                <div class="section-progress-bar-bg">
                    <div class="section-progress-bar-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['pct']>=80?'#22C55E':($s['pct']>=40?'#F59E0B':'#EF4444') }};"></div>
                </div>
                <span style="font-size:.78rem;font-weight:700;white-space:nowrap;">{{ $s['pct'] }}%</span>
            </div>
            <form method="POST" action="{{ route('portal.expediente.datos') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre(s)</label>
                        <input type="text" name="first_name" class="form-input" value="{{ old('first_name', $client->first_name) }}" placeholder="María Fernanda">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" name="last_name_paterno" class="form-input" value="{{ old('last_name_paterno', $client->last_name_paterno) }}" placeholder="González">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" name="last_name_materno" class="form-input" value="{{ old('last_name_materno', $client->last_name_materno) }}" placeholder="Ríos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="birth_date" class="form-input" value="{{ old('birth_date', $client->birth_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado de Nacimiento</label>
                        <select name="birth_state" class="form-select">
                            <option value="">Seleccionar</option>
                            @foreach($estados as $e)
                            <option value="{{ $e }}" {{ old('birth_state',$client->birth_state)===$e?'selected':'' }}>{{ $e }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sexo</label>
                        <div style="display:flex;gap:1rem;padding-top:.3rem;">
                            <label style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;cursor:pointer;">
                                <input type="radio" name="gender" value="H" {{ old('gender',$client->gender)==='H'?'checked':'' }}> Hombre
                            </label>
                            <label style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;cursor:pointer;">
                                <input type="radio" name="gender" value="M" {{ old('gender',$client->gender)==='M'?'checked':'' }}> Mujer
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nacionalidad</label>
                        <input type="text" name="nationality" class="form-input" value="{{ old('nationality', $client->nationality ?? 'mexicana') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado Civil</label>
                        <select name="marital_status" class="form-select" id="exp_marital">
                            <option value="">Seleccionar</option>
                            <option value="soltero"     {{ old('marital_status',$client->marital_status)==='soltero'?'selected':'' }}>Soltero/a</option>
                            <option value="casado"      {{ old('marital_status',$client->marital_status)==='casado'?'selected':'' }}>Casado/a</option>
                            <option value="divorciado"  {{ old('marital_status',$client->marital_status)==='divorciado'?'selected':'' }}>Divorciado/a</option>
                            <option value="viudo"       {{ old('marital_status',$client->marital_status)==='viudo'?'selected':'' }}>Viudo/a</option>
                            <option value="union_libre" {{ old('marital_status',$client->marital_status)==='union_libre'?'selected':'' }}>Unión Libre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ocupación / Profesión</label>
                        <input type="text" name="occupation" class="form-input" value="{{ old('occupation', $client->occupation) }}" placeholder="Ingeniero, Contador...">
                    </div>
                </div>

                {{-- Cónyuge (condicional) --}}
                <div id="exp-spouse" style="display:{{ in_array($client->marital_status,['casado','union_libre'])?'block':'none' }};">
                    <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Datos del Cónyuge</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Régimen Patrimonial</label>
                            <select name="marital_regime" class="form-select">
                                <option value="">Seleccionar</option>
                                <option value="separacion_bienes" {{ old('marital_regime',$client->marital_regime)==='separacion_bienes'?'selected':'' }}>Separación de Bienes</option>
                                <option value="sociedad_conyugal"  {{ old('marital_regime',$client->marital_regime)==='sociedad_conyugal'?'selected':'' }}>Sociedad Conyugal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nombre del Cónyuge</label>
                            <input type="text" name="spouse_name" class="form-input" value="{{ old('spouse_name', $client->spouse_name) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CURP del Cónyuge</label>
                            <input type="text" name="spouse_curp" class="form-input" value="{{ old('spouse_curp', $client->spouse_curp) }}" maxlength="18" style="text-transform:uppercase;">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar datos personales</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     SECCIÓN 2: IDENTIFICACIÓN Y DOMICILIO
════════════════════════════════════════════════════════════ --}}
<div class="exp-section" id="sec-identificacion">
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header"><span style="font-size:.85rem;font-weight:700;">Identificación oficial y domicilio legal</span></div>
        <div class="card-body">
            @php $s = $sections['identificacion']; @endphp
            <div class="section-progress">
                <span style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">{{ $s['filled'] }}/{{ $s['total'] }} campos</span>
                <div class="section-progress-bar-bg">
                    <div class="section-progress-bar-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['pct']>=80?'#22C55E':($s['pct']>=40?'#F59E0B':'#EF4444') }};"></div>
                </div>
                <span style="font-size:.78rem;font-weight:700;white-space:nowrap;">{{ $s['pct'] }}%</span>
            </div>
            <form method="POST" action="{{ route('portal.expediente.datos') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">CURP</label>
                        <input type="text" name="curp" class="form-input" value="{{ old('curp',$client->curp) }}" maxlength="18" style="text-transform:uppercase;" placeholder="XXXX000000XXXXXXXX">
                        @if($client->curp_verified_at)<p class="form-hint" style="color:#166534;">✓ Verificado</p>@endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" class="form-input" value="{{ old('rfc',$client->rfc) }}" maxlength="13" style="text-transform:uppercase;" placeholder="XXXX000000XXX">
                        @if($client->rfc_verified_at)<p class="form-hint" style="color:#166534;">✓ Verificado</p>@endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Identificación</label>
                        <select name="id_type" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="INE"              {{ old('id_type',$client->id_type)==='INE'?'selected':'' }}>INE / IFE</option>
                            <option value="pasaporte"        {{ old('id_type',$client->id_type)==='pasaporte'?'selected':'' }}>Pasaporte</option>
                            <option value="cedula_profesional" {{ old('id_type',$client->id_type)==='cedula_profesional'?'selected':'' }}>Cédula Profesional</option>
                            <option value="otro"             {{ old('id_type',$client->id_type)==='otro'?'selected':'' }}>Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de Identificación</label>
                        <input type="text" name="id_number" class="form-input" value="{{ old('id_number',$client->id_number) }}" placeholder="Folio o número">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vigencia</label>
                        <input type="date" name="id_expiry" class="form-input" value="{{ old('id_expiry',$client->id_expiry?->format('Y-m-d')) }}">
                        @if($client->id_expiry?->isPast())<p class="form-hint" style="color:var(--danger);">⚠ Identificación vencida</p>@endif
                    </div>
                </div>

                <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Domicilio para contratos</div>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Calle y Número</label>
                        <input type="text" name="address_street" class="form-input" value="{{ old('address_street',$client->address_street) }}" placeholder="Av. Insurgentes Sur 1234, Int. 5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Colonia</label>
                        <input type="text" name="address_colony" class="form-input" value="{{ old('address_colony',$client->address_colony) }}" placeholder="Del Valle">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alcaldía / Municipio</label>
                        <input type="text" name="address_municipality" class="form-input" value="{{ old('address_municipality',$client->address_municipality) }}" placeholder="Benito Juárez">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="address_state" class="form-select">
                            <option value="">Seleccionar</option>
                            @foreach($estados as $e)
                            <option value="{{ $e }}" {{ old('address_state',$client->address_state)===$e?'selected':'' }}>{{ $e }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Código Postal</label>
                        <input type="text" name="address_zip" class="form-input" value="{{ old('address_zip',$client->address_zip) }}" maxlength="5" placeholder="03100">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar identificación y domicilio</button>
                </div>
            </form>

            {{-- Documentos de identificación --}}
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Documentos de identificación</div>
                @foreach(['ine_frente'=>'INE — Frente','ine_reverso'=>'INE — Reverso','comprobante_domicilio'=>'Comprobante de Domicilio','acta_nacimiento'=>'Acta de Nacimiento'] as $cat => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:.82rem;">
                        @if($documents->has($cat))
                        <span style="color:#166534;">✅</span>
                        @else
                        <span style="color:var(--text-muted);">○</span>
                        @endif
                        {{ $label }}
                    </span>
                    @if($documents->has($cat))
                    <span style="font-size:.72rem;color:var(--text-muted);">Subido</span>
                    @else
                    <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data" style="display:flex;gap:.4rem;align-items:center;">
                        @csrf
                        <input type="hidden" name="category" value="{{ $cat }}">
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.75rem;max-width:160px;" onchange="this.form.submit()">
                    </form>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Documentos personales del vendedor (checklist real de la notaría, 2026-07-07).
                 comprobante_domicilio se excluye aquí a propósito — ya se pide arriba
                 en "Documentos de identificación", mismo category key, no duplicar la fila. --}}
            @if($isVendedor)
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Documentos personales (vendedor)</div>
                @foreach(collect(\App\Support\SellerDocumentChecklist::PERSONAL)->except('comprobante_domicilio') as $cat => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:.82rem;">
                        @if($documents->has($cat))
                        <span style="color:#166534;">✅</span>
                        @else
                        <span style="color:var(--text-muted);">○</span>
                        @endif
                        {{ $label }}
                    </span>
                    @if($documents->has($cat))
                    <span style="font-size:.72rem;color:var(--text-muted);">Subido</span>
                    @else
                    <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data" style="display:flex;gap:.4rem;align-items:center;">
                        @csrf
                        <input type="hidden" name="category" value="{{ $cat }}">
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.75rem;max-width:160px;" onchange="this.form.submit()">
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     SECCIÓN 3: DOCUMENTOS DEL INMUEBLE (arrendador/vendedor)
════════════════════════════════════════════════════════════ --}}
@if($isArrendador || $isVendedor)
<div class="exp-section" id="sec-inmueble">
    <div class="card">
        <div class="card-header"><span style="font-size:.85rem;font-weight:700;">Documentos del inmueble</span></div>
        <div class="card-body">
            @php $s = $sections['documentos_inmueble']; @endphp
            <div class="section-progress">
                <span style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">{{ $s['filled'] }}/{{ $s['total'] }} documentos</span>
                <div class="section-progress-bar-bg">
                    <div class="section-progress-bar-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['pct']>=80?'#22C55E':($s['pct']>=40?'#F59E0B':'#EF4444') }};"></div>
                </div>
                <span style="font-size:.78rem;font-weight:700;white-space:nowrap;">{{ $s['pct'] }}%</span>
            </div>
            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.5;">
                Sube los documentos de tu inmueble para acelerar el proceso. Puedes subir fotos o PDFs directamente desde tu teléfono.
            </p>
            @foreach(\App\Support\SellerDocumentChecklist::INMUEBLE as $cat => $label)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 0;border-bottom:1px solid var(--border);">
                <div style="font-size:.83rem;">
                    @if($documents->has($cat))
                    <span style="color:#166534;">✅</span>
                    @else
                    <span style="color:var(--text-muted);">○</span>
                    @endif
                    <strong style="margin-left:.3rem;">{{ $label }}</strong>
                </div>
                @if($documents->has($cat))
                <div>
                    @foreach($documents->get($cat) as $doc)
                    <a href="{{ route('portal.documents.download', $doc->id) }}" style="font-size:.75rem;color:var(--primary);">Ver →</a>
                    @endforeach
                </div>
                @else
                <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data" style="display:flex;gap:.4rem;align-items:center;">
                    @csrf
                    @if($rentalAsOwner)<input type="hidden" name="rental_process_id" value="{{ $rentalAsOwner->id }}">@endif
                    <input type="hidden" name="category" value="{{ $cat }}">
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.75rem;max-width:180px;" onchange="this.form.submit()">
                </form>
                @endif
            </div>
            @endforeach

            {{-- Documentos según estado civil (solo vendedor — casado/unión libre/divorciado) --}}
            @if($isVendedor && ($civilDocs = \App\Support\SellerDocumentChecklist::estadoCivilDocs($client->marital_status)))
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Documentos según estado civil</div>
                @foreach($civilDocs as $cat => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 0;border-bottom:1px solid var(--border);">
                    <div style="font-size:.83rem;">
                        @if($documents->has($cat))
                        <span style="color:#166534;">✅</span>
                        @else
                        <span style="color:var(--text-muted);">○</span>
                        @endif
                        <strong style="margin-left:.3rem;">{{ $label }}</strong>
                    </div>
                    @if($documents->has($cat))
                    <div>
                        @foreach($documents->get($cat) as $doc)
                        <a href="{{ route('portal.documents.download', $doc->id) }}" style="font-size:.75rem;color:var(--primary);">Ver →</a>
                        @endforeach
                    </div>
                    @else
                    <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data" style="display:flex;gap:.4rem;align-items:center;">
                        @csrf
                        <input type="hidden" name="category" value="{{ $cat }}">
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.75rem;max-width:180px;" onchange="this.form.submit()">
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Documentación notarial (solo vendedor, solo lectura — la tramita
         la notaría, no se le pide subir al cliente). Misma exp-section
         que "Documentos del inmueble", no una nueva — evita un id duplicado
         que rompería el toggle de tabs por JS. --}}
    @if($isVendedor)
    <div class="card" style="margin-top:1rem;">
        <div class="card-header"><span style="font-size:.85rem;font-weight:700;">Documentación notarial</span></div>
        <div class="card-body">
            <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.5;">
                Esta documentación la tramita la notaría directamente — aquí solo se muestra el estado, no necesitas subir nada.
            </p>
            @foreach(\App\Support\SellerDocumentChecklist::NOTARIAL as $cat => $label)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);">
                <span style="font-size:.82rem;">
                    @if($documents->has($cat))
                    <span style="color:#166534;">✅</span>
                    @else
                    <span style="color:var(--text-muted);">○ pendiente</span>
                    @endif
                    {{ $label }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     SECCIÓN 4: FINANCIAMIENTO (comprador)
════════════════════════════════════════════════════════════ --}}
@if($isComprador)
<div class="exp-section" id="sec-financiamiento">
    <div class="card">
        <div class="card-header"><span style="font-size:.85rem;font-weight:700;">Tipo de financiamiento</span></div>
        <div class="card-body">
            @php $s = $sections['financiamiento'] ?? ['filled'=>0,'total'=>1,'pct'=>0]; @endphp
            <div class="section-progress">
                <span style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">{{ $s['filled'] }}/{{ $s['total'] }} campos</span>
                <div class="section-progress-bar-bg">
                    <div class="section-progress-bar-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['pct']>=80?'#22C55E':($s['pct']>=40?'#F59E0B':'#EF4444') }};"></div>
                </div>
                <span style="font-size:.78rem;font-weight:700;white-space:nowrap;">{{ $s['pct'] }}%</span>
            </div>
            <form method="POST" action="{{ route('portal.expediente.financiamiento') }}">
                @csrf
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label">¿Cómo planeas pagar?</label>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.6rem;margin-top:.4rem;">
                        @foreach(['contado'=>['icon'=>'💵','label'=>'Contado'],'hipotecario'=>['icon'=>'🏦','label'=>'Crédito hipotecario'],'infonavit'=>['icon'=>'🏗️','label'=>'Infonavit'],'fovissste'=>['icon'=>'🏛️','label'=>'Fovissste'],'cofinanciamiento'=>['icon'=>'🤝','label'=>'Cofinanciamiento']] as $val => $opt)
                        <label style="border:2px solid {{ old('financing_type',$client->financing_type)===$val?'#0E304B':'var(--border)' }};background:{{ old('financing_type',$client->financing_type)===$val?'#EFF6FF':'var(--card)' }};border-radius:var(--radius);padding:.75rem .5rem;text-align:center;cursor:pointer;font-size:.8rem;font-weight:600;">
                            <input type="radio" name="financing_type" value="{{ $val }}" {{ old('financing_type',$client->financing_type)===$val?'checked':'' }} style="display:none;">
                            <div style="font-size:1.4rem;margin-bottom:.3rem;">{{ $opt['icon'] }}</div>
                            {{ $opt['label'] }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-grid" id="fin-extra">
                    @if(in_array($client->financing_type,['hipotecario','cofinanciamiento']))
                    <div class="form-group">
                        <label class="form-label">Monto preautorizado</label>
                        <input type="number" name="financing_preauth_amount" class="form-input" value="{{ old('financing_preauth_amount',$client->financing_preauth_amount) }}" placeholder="0.00" step="0.01" min="0">
                    </div>
                    @endif
                    @if($client->financing_type === 'infonavit')
                    <div class="form-group">
                        <label class="form-label">NSS (Número de Seguro Social)</label>
                        <input type="text" name="nss" class="form-input" value="{{ old('nss',$client->nss) }}" maxlength="11" placeholder="11 dígitos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Saldo de Subcuenta de Vivienda</label>
                        <input type="number" name="infonavit_balance" class="form-input" value="{{ old('infonavit_balance',$client->infonavit_balance) }}" placeholder="0.00" step="0.01" min="0">
                    </div>
                    @endif
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar financiamiento</button>
                </div>
            </form>

            {{-- Carta de preautorización --}}
            @if(in_array($client->financing_type,['hipotecario','cofinanciamiento']))
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
                <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.6rem;">Carta de Preautorización</div>
                @if($documents->has('carta_preautorizacion'))
                <span style="color:#166534;font-size:.83rem;">✅ Documento subido</span>
                @else
                <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="category" value="carta_preautorizacion">
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.82rem;" onchange="this.form.submit()">
                </form>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     SECCIÓN 5: INGRESOS (arrendatario)
════════════════════════════════════════════════════════════ --}}
@if($isArrendatario)
<div class="exp-section" id="sec-ingresos">
    <div class="card">
        <div class="card-header"><span style="font-size:.85rem;font-weight:700;">Comprobación de ingresos</span></div>
        <div class="card-body">
            @php $s = $sections['ingresos'] ?? ['filled'=>0,'total'=>3,'pct'=>0]; @endphp
            <div class="section-progress">
                <span style="font-size:.78rem;color:var(--text-muted);white-space:nowrap;">{{ $s['filled'] }}/{{ $s['total'] }} elementos</span>
                <div class="section-progress-bar-bg">
                    <div class="section-progress-bar-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['pct']>=80?'#22C55E':($s['pct']>=40?'#F59E0B':'#EF4444') }};"></div>
                </div>
                <span style="font-size:.78rem;font-weight:700;white-space:nowrap;">{{ $s['pct'] }}%</span>
            </div>
            <form method="POST" action="{{ route('portal.expediente.ingresos') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tipo de ingresos</label>
                        <select name="income_type" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="empleado"      {{ old('income_type',$client->income_type)==='empleado'?'selected':'' }}>Empleado (nómina)</option>
                            <option value="independiente" {{ old('income_type',$client->income_type)==='independiente'?'selected':'' }}>Independiente / Honorarios</option>
                            <option value="empresario"    {{ old('income_type',$client->income_type)==='empresario'?'selected':'' }}>Empresario / Sociedad</option>
                            <option value="otro"          {{ old('income_type',$client->income_type)==='otro'?'selected':'' }}>Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ingreso mensual promedio</label>
                        <input type="number" name="income_amount" class="form-input" value="{{ old('income_amount',$client->income_amount) }}" placeholder="0.00" step="0.01" min="0">
                        <p class="form-hint">Este dato es confidencial y solo lo ve tu asesor.</p>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar ingresos</button>
                </div>
            </form>

            {{-- Documentos de ingresos --}}
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border);">
                <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.75rem;">Comprobantes de ingresos</div>
                @foreach(['nomina'=>'Recibos de Nómina (últimos 3)','estado_cuenta'=>'Estado de Cuenta (últimos 3 meses)','cfdi_honorarios'=>'CFDI de Honorarios','proof_of_income'=>'Otro comprobante de ingresos'] as $cat => $label)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:.82rem;">
                        {{ $documents->has($cat) ? '✅' : '○' }} {{ $label }}
                    </span>
                    @if(!$documents->has($cat))
                    <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data" style="display:flex;gap:.4rem;">
                        @csrf
                        @if($rentalAsInquilino)<input type="hidden" name="rental_process_id" value="{{ $rentalAsInquilino->id }}">@endif
                        <input type="hidden" name="category" value="{{ $cat }}">
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.75rem;max-width:160px;" onchange="this.form.submit()">
                    </form>
                    @else
                    <span style="font-size:.72rem;color:var(--text-muted);">Subido ✓</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     SECCIÓN 6: GARANTÍA (arrendatario)
════════════════════════════════════════════════════════════ --}}
<div class="exp-section" id="sec-garantia">
    <div class="card">
        <div class="card-header"><span style="font-size:.85rem;font-weight:700;">Tipo de garantía</span></div>
        <div class="card-body">

            {{-- Tipo de garantía definida por asesor --}}
            @if($guaranteeType)
            <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:8px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.83rem;color:#1D4ED8;">
                Tu asesor ha definido la garantía requerida:
                <strong>{{ \App\Models\RentalProcess::GUARANTEE_TYPES[$guaranteeType] ?? $guaranteeType }}</strong>
            </div>
            @else
            <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:1rem;">Tu asesor definirá el tipo de garantía. Puedes preparar la información mientras tanto.</p>
            @endif

            {{-- AVAL --}}
            @if(!$guaranteeType || $hasAval)
            <div style="margin-bottom:1.5rem;">
                <div style="font-size:.85rem;font-weight:700;margin-bottom:.75rem;">
                    👤 Datos del Aval / Fiador
                </div>
                <form method="POST" action="{{ route('portal.expediente.aval') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nombre completo del aval <span class="required">*</span></label>
                            <input type="text" name="name" class="form-input" value="{{ old('name',$aval?->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Relación / Parentesco</label>
                            <input type="text" name="relationship" class="form-input" value="{{ old('relationship',$aval?->relationship) }}" placeholder="Familiar, amigo, socio...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="phone" class="form-input" value="{{ old('phone',$aval?->phone) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="{{ old('email',$aval?->email) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">CURP del aval</label>
                            <input type="text" name="curp" class="form-input" value="{{ old('curp',$aval?->curp) }}" maxlength="18" style="text-transform:uppercase;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de ID</label>
                            <select name="id_type" class="form-select">
                                <option value="">Seleccionar</option>
                                <option value="INE" {{ old('id_type',$aval?->id_type)==='INE'?'selected':'' }}>INE</option>
                                <option value="pasaporte" {{ old('id_type',$aval?->id_type)==='pasaporte'?'selected':'' }}>Pasaporte</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Número de ID</label>
                            <input type="text" name="id_number" class="form-input" value="{{ old('id_number',$aval?->id_number) }}">
                        </div>
                    </div>

                    <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin:1rem 0 .6rem;">Inmueble del aval en garantía</div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Dirección del inmueble</label>
                            <input type="text" name="property_address" class="form-input" value="{{ old('property_address',$aval?->property_address) }}" placeholder="Calle, número, colonia">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Municipio</label>
                            <input type="text" name="property_municipality" class="form-input" value="{{ old('property_municipality',$aval?->property_municipality) }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select name="property_state" class="form-select">
                                <option value="">Seleccionar</option>
                                @foreach($estados as $e)
                                <option value="{{ $e }}" {{ old('property_state',$aval?->property_state)===$e?'selected':'' }}>{{ $e }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Valor aproximado</label>
                            <input type="number" name="property_value" class="form-input" value="{{ old('property_value',$aval?->property_value) }}" placeholder="0.00" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Folio registral</label>
                            <input type="text" name="property_folio_real" class="form-input" value="{{ old('property_folio_real',$aval?->property_folio_real) }}" placeholder="Número de folio">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="margin-bottom:.5rem;">Estado del inmueble</label>
                            <div style="display:flex;flex-direction:column;gap:.4rem;">
                                <label style="font-size:.83rem;cursor:pointer;display:flex;align-items:center;gap:.4rem;">
                                    <input type="checkbox" name="property_free_of_liens" value="1" {{ old('property_free_of_liens',$aval?->property_free_of_liens)?'checked':'' }}>
                                    Libre de gravamen
                                </label>
                                <label style="font-size:.83rem;cursor:pointer;display:flex;align-items:center;gap:.4rem;">
                                    <input type="checkbox" name="property_has_mortgage" value="1" {{ old('property_has_mortgage',$aval?->property_has_mortgage)?'checked':'' }}>
                                    Tiene hipoteca
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar datos del aval</button>
                    </div>
                </form>

                {{-- Docs del aval --}}
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
                    <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.6rem;">Documentos del aval</div>
                    @foreach(['aval_ine_frente'=>'INE Aval (frente)','aval_ine_reverso'=>'INE Aval (reverso)','aval_escritura'=>'Escritura del inmueble','aval_predial'=>'Predial','aval_libertad_gravamen'=>'Libertad de gravamen'] as $cat => $label)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--border);">
                        <span style="font-size:.82rem;">{{ $documents->has($cat)?'✅':'○' }} {{ $label }}</span>
                        @if(!$documents->has($cat))
                        <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data">
                            @csrf
                            @if($rentalAsInquilino)<input type="hidden" name="rental_process_id" value="{{ $rentalAsInquilino->id }}">@endif
                            <input type="hidden" name="category" value="{{ $cat }}">
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.75rem;max-width:150px;" onchange="this.form.submit()">
                        </form>
                        @else
                        <span style="font-size:.72rem;color:var(--text-muted);">Subido ✓</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- PAGARÉS --}}
            @if($hasPagares && $rentalAsInquilino?->pagares->count())
            <div style="{{ $hasAval ? 'margin-top:1.5rem;padding-top:1.5rem;border-top:2px solid var(--border);' : '' }}">
                <div style="font-size:.85rem;font-weight:700;margin-bottom:.75rem;">📄 Pagarés</div>
                @foreach($rentalAsInquilino->pagares as $pagare)
                <div class="pagare-card">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem;">
                        <div>
                            <span style="font-weight:700;font-size:.9rem;">{{ $pagare->quantity }} pagaré{{ $pagare->quantity>1?'s':'' }}</span>
                            <span style="font-size:.8rem;color:var(--text-muted);margin-left:.5rem;">de ${{ number_format($pagare->amount_each, 0) }} MXN c/u</span>
                        </div>
                        <span style="font-size:.75rem;font-weight:700;padding:2px 8px;border-radius:12px;
                            background:{{ $pagare->status==='signed'?'#dcfce7':($pagare->status==='returned'?'#f0fdf4':'#fef9c3') }};
                            color:{{ $pagare->status==='signed'?'#166534':($pagare->status==='returned'?'#166534':'#92400e') }};">
                            {{ $pagare->status_label }}
                        </span>
                    </div>
                    <div style="font-size:.8rem;color:var(--text-muted);">
                        Total: <strong style="color:#92400e;">${{ number_format($pagare->total_amount, 0) }} MXN</strong>
                        @if($pagare->issue_date) &nbsp;· Firma: {{ $pagare->issue_date->format('d/m/Y') }} @endif
                        @if($pagare->beneficiary_name) &nbsp;· A favor de: {{ $pagare->beneficiary_name }} @endif
                    </div>
                    @if($pagare->notes)<p style="font-size:.78rem;color:var(--text-muted);margin-top:.4rem;">{{ $pagare->notes }}</p>@endif
                </div>
                @endforeach
                <p style="font-size:.78rem;color:var(--text-muted);">Los pagarés son gestionados por tu asesor. Si tienes dudas, contáctalo.</p>
            </div>
            @endif

            {{-- PÓLIZA JURÍDICA --}}
            @if($hasPoliza)
            <div style="{{ ($hasAval||$hasPagares)?'margin-top:1.5rem;padding-top:1.5rem;border-top:2px solid var(--border);':'' }}">
                <div style="font-size:.85rem;font-weight:700;margin-bottom:.5rem;">🛡️ Póliza Jurídica</div>
                @if($rentalAsInquilino?->poliza_aseguradora)
                <p style="font-size:.83rem;color:var(--text);">
                    <strong>{{ $rentalAsInquilino->poliza_aseguradora }}</strong>
                    @if($rentalAsInquilino->poliza_number) · Póliza: {{ $rentalAsInquilino->poliza_number }} @endif
                </p>
                @else
                <p style="font-size:.82rem;color:var(--text-muted);">Tu asesor coordinará la póliza jurídica. Te contactará cuando esté lista.</p>
                @endif
                {{-- Upload póliza doc --}}
                <div style="margin-top:.75rem;">
                    @if($documents->has('poliza_contract'))
                    <span style="color:#166534;font-size:.83rem;">✅ Póliza subida</span>
                    @else
                    <form method="POST" action="{{ route('portal.expediente.upload') }}" enctype="multipart/form-data">
                        @csrf
                        @if($rentalAsInquilino)<input type="hidden" name="rental_process_id" value="{{ $rentalAsInquilino->id }}">@endif
                        <input type="hidden" name="category" value="poliza_contract">
                        <p class="form-hint" style="margin-bottom:.4rem;">Sube tu copia de la póliza cuando la tengas:</p>
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" style="font-size:.82rem;" onchange="this.form.submit()">
                    </form>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function showSection(id, btn) {
    document.querySelectorAll('.exp-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.exp-tab').forEach(t => t.classList.remove('active'));
    var sec = document.getElementById('sec-' + id);
    if (sec) sec.classList.add('active');
    if (btn) btn.classList.add('active');
}

// Show spouse section based on marital status
var msEl = document.getElementById('exp_marital');
if (msEl) {
    msEl.addEventListener('change', function(){
        var show = ['casado','union_libre'].includes(this.value);
        var sp = document.getElementById('exp-spouse');
        if (sp) sp.style.display = show ? 'block' : 'none';
    });
}

// Financing type radio styling
document.querySelectorAll('input[name="financing_type"]').forEach(function(r){
    r.addEventListener('change', function(){
        document.querySelectorAll('input[name="financing_type"]').forEach(function(x){
            var lbl = x.closest('label');
            if (lbl) {
                lbl.style.borderColor = x.checked ? '#0E304B' : 'var(--border)';
                lbl.style.background  = x.checked ? '#EFF6FF' : 'var(--card)';
            }
        });
    });
});

// If success flash and we know which section, show it
@if(session('success'))
// Default stay on first tab
@endif
</script>
@endsection
