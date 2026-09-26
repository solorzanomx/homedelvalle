{{-- Arrendador anterior de una persona del trato (inquilino u obligado): ver, llamar y capturar/corregir por teléfono.
     Vive DENTRO del formulario de investigación: sin <form> anidado (hdvLandlordSave crea uno aparte). --}}
@php
    $whoKey = $who === 'inquilino' ? 'tenant' : 'obligado';
    $lid = 'pl-' . $whoKey;
    $has = $person && ($person->previous_landlord_name || $person->previous_landlord_phone || $person->previous_landlord_mobile || $person->previous_landlord_address);
    $digits = fn($p) => preg_replace('/\D/', '', (string) $p);
    $wa = fn($p) => (function ($d) { return strlen($d) === 10 ? '52' . $d : $d; })(preg_replace('/\D/', '', (string) $p));
    $inp = 'font-size:.8rem;padding:.35rem .5rem;';
    $f = fn($k, $ph, $span = false) => '<input class="form-input" data-f="' . $k . '" placeholder="' . $ph . '" value="' . e($person?->{$k}) . '" style="' . ($span ? 'grid-column:1/-1;' : '') . $inp . '">';
@endphp
<div style="grid-column:1/-1;">
    <div style="font-size:.78rem;font-weight:700;margin-bottom:.45rem;">
        Arrendador anterior del {{ $who }}@if($person && $who !== 'inquilino') · {{ $person->name }}@endif
        <span class="badge {{ $has ? 'badge-green' : 'badge-red' }}">{{ $has ? 'capturado' : 'falta' }}</span>
    </div>
    @if($has)
    <div style="border:1px solid var(--border);border-radius:8px;padding:.6rem .8rem;font-size:.82rem;line-height:1.5;">
        <strong>{{ $person->previous_landlord_name ?: '—' }}</strong>@if($person->previous_landlord_years) <span style="color:var(--text-muted);">· rentó {{ $person->previous_landlord_years }}</span>@endif
        @if($person->previous_landlord_address)<div style="color:var(--text-muted);">📍 Inmueble: {{ $person->previous_landlord_address }}</div>@endif
        <div style="display:flex;gap:.9rem;flex-wrap:wrap;margin-top:.15rem;">
            @if($person->previous_landlord_mobile)
                <span>📱 {{ $person->previous_landlord_mobile }} <a href="tel:{{ $digits($person->previous_landlord_mobile) }}" style="margin-left:.3rem;">Llamar</a> ·
                    <a href="https://wa.me/{{ $wa($person->previous_landlord_mobile) }}?text={{ rawurlencode('Hola, le escribo de Home del Valle: ' . $person->name . ' lo(a) dio como su arrendador anterior. ¿Podría contestarme unas preguntas breves?') }}" target="_blank" rel="noopener">WhatsApp</a></span>
            @endif
            @if($person->previous_landlord_phone)<span>☎️ {{ $person->previous_landlord_phone }} <a href="tel:{{ $digits($person->previous_landlord_phone) }}" style="margin-left:.3rem;">Llamar</a></span>@endif
            @if($person->previous_landlord_email)<span>✉️ <a href="mailto:{{ $person->previous_landlord_email }}">{{ $person->previous_landlord_email }}</a></span>@endif
        </div>
    </div>
    @endif
    <details style="margin-top:.35rem;" {{ $has ? '' : 'open' }}>
        <summary style="cursor:pointer;font-size:.78rem;font-weight:700;color:var(--primary);">{{ $has ? '✏️ Editar datos del arrendador anterior' : '＋ Capturar arrendador anterior (por teléfono, a nombre del cliente)' }}</summary>
        <div id="{{ $lid }}" style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.4rem;">
            {!! $f('previous_landlord_name', 'Nombre del arrendador', true) !!}
            {!! $f('previous_landlord_mobile', 'Celular') !!}
            {!! $f('previous_landlord_phone', 'Teléfono fijo') !!}
            {!! $f('previous_landlord_email', 'Email', true) !!}
            {!! $f('previous_landlord_address', 'Dirección del inmueble que rentaba', true) !!}
            {!! $f('previous_landlord_years', 'Tiempo de arrendamiento (ej. 3 años)', true) !!}
            <button type="button" class="btn btn-sm btn-primary" style="grid-column:1/-1;" onclick="hdvLandlordSave('{{ route('rentals.previous-landlord.save', $rental->id) }}', '{{ $lid }}', '{{ $whoKey }}')">Guardar arrendador anterior</button>
        </div>
    </details>
</div>
@once
<script>
    function hdvLandlordSave(url, boxId, who) {
        var box = document.getElementById(boxId), f = document.createElement('form'); f.method = 'POST'; f.action = url;
        var add = function (n, v) { var i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; f.appendChild(i); };
        add('_token', '{{ csrf_token() }}'); add('who', who);
        box.querySelectorAll('[data-f]').forEach(function (el) { add(el.getAttribute('data-f'), el.value); });
        document.body.appendChild(f); f.submit();
    }
</script>
@endonce
