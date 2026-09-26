{{-- Referencias personales de una persona del trato (inquilino u obligado solidario) con botón de rechazo.
     Este parcial vive DENTRO del formulario de investigación: nunca anidar <form> aquí (hdvRefAction crea uno aparte). --}}
@php
    $refs = ($person?->references ?? collect())->sortBy('sort_order')->values();
    $validCount = $refs->filter(fn($r) => ! $r->isRejected())->count();
    $waDigits = fn($p) => (function ($d) { return strlen($d) === 10 ? '52' . $d : $d; })(preg_replace('/\D/', '', (string) $p));
    $personPhone = preg_replace('/\D/', '', (string) $person?->phone);
    $personAddr = mb_strtolower(trim((string) ($person?->address_street ?: $person?->address)));
    $reasons = ['Es familiar directo (mamá, papá, hermano…)', 'Vive en la misma casa que el ' . $who, 'No contesta o el número no funciona', 'No conoce a la persona', 'Otro motivo'];
    $whoKey = $who === 'inquilino' ? 'tenant' : 'obligado';
    $usedSlots = $refs->reject(fn($r) => $r->isRejected())->pluck('sort_order')->all();
    $freeSlots = array_values(array_diff([1, 2, 3], $usedSlots));
    $uid = $whoKey . '-' . ($person?->id ?? 0);
    $inp = 'font-size:.8rem;padding:.35rem .5rem;';
@endphp
@php
    // Formulario de captura (alta o edición) — se envía con hdvRefSave, sin <form> anidado.
    $refForm = function ($fid, $slot, $r = null) use ($rental, $whoKey, $inp) {
        $h = '<div id="rf-' . $fid . '" style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem;margin-top:.4rem;">'
           . '<input class="form-input" data-f="name" placeholder="Nombre completo" value="' . e($r?->name) . '" style="grid-column:1/-1;' . $inp . '">'
           . '<input class="form-input" data-f="mobile_phone" placeholder="Celular" value="' . e($r?->mobile_phone) . '" style="' . $inp . '">'
           . '<input class="form-input" data-f="landline_phone" placeholder="Teléfono fijo" value="' . e($r?->landline_phone) . '" style="' . $inp . '">'
           . '<input class="form-input" data-f="address" placeholder="Dirección" value="' . e($r?->address) . '" style="grid-column:1/-1;' . $inp . '">'
           . '<input class="form-input" data-f="email" placeholder="Email" value="' . e($r?->email) . '" style="grid-column:1/-1;' . $inp . '">'
           . '<button type="button" class="btn btn-sm btn-primary" style="grid-column:1/-1;" onclick="hdvRefSave(\'' . route('rentals.references.save', $rental->id) . '\', \'rf-' . $fid . '\', \'' . $whoKey . '\', ' . $slot . ')">Guardar referencia</button>'
           . '</div>';
        return $h;
    };
@endphp
<div style="grid-column:1/-1;">
    <div style="font-size:.78rem;font-weight:700;margin-bottom:.45rem;">
        Referencias personales del {{ $who }}@if($person && $who !== 'inquilino') · {{ $person->name }}@endif
        <span class="badge {{ $validCount >= 3 ? 'badge-green' : ($validCount > 0 ? 'badge-yellow' : 'badge-red') }}">{{ $validCount }} de 3 válidas</span>
    </div>
    @forelse($refs as $i => $ref)
        @php
            $sameHome = ($ref->address && $personAddr && mb_strtolower(trim($ref->address)) === $personAddr)
                || ($personPhone && $personPhone === preg_replace('/\D/', '', (string) $ref->mobile_phone));
        @endphp
        <div style="border:1px solid {{ $ref->isRejected() ? '#fecaca' : 'var(--border)' }};background:{{ $ref->isRejected() ? '#fef2f2' : 'transparent' }};border-radius:8px;padding:.6rem .8rem;margin-bottom:.45rem;font-size:.82rem;line-height:1.5;">
            <strong style="{{ $ref->isRejected() ? 'text-decoration:line-through;color:#991b1b;' : '' }}">{{ $i + 1 }}. {{ $ref->name }}</strong>
            @if($ref->isRejected())<span class="badge badge-red">Rechazada</span>@endif
            @if($sameHome && ! $ref->isRejected())<span class="badge badge-yellow" title="Coincide el teléfono o el domicilio con el de la persona">⚠ mismo domicilio/teléfono</span>@endif
            @if($ref->address)<div style="color:var(--text-muted);">📍 {{ $ref->address }}</div>@endif
            <div style="display:flex;gap:.9rem;flex-wrap:wrap;margin-top:.15rem;">
                @if($ref->mobile_phone)
                    <span>📱 {{ $ref->mobile_phone }}
                        <a href="tel:{{ preg_replace('/\D/', '', $ref->mobile_phone) }}" style="margin-left:.3rem;">Llamar</a> ·
                        <a href="https://wa.me/{{ $waDigits($ref->mobile_phone) }}?text={{ rawurlencode('Hola, le escribo de Home del Valle: ' . ($person?->name ?? 'una persona') . ' lo(a) dio como referencia personal para una renta. ¿Podría contestarme unas preguntas breves?') }}" target="_blank" rel="noopener">WhatsApp</a></span>
                @endif
                @if($ref->landline_phone)<span>☎️ {{ $ref->landline_phone }} <a href="tel:{{ preg_replace('/\D/', '', $ref->landline_phone) }}" style="margin-left:.3rem;">Llamar</a></span>@endif
                @if($ref->email)<span>✉️ <a href="mailto:{{ $ref->email }}">{{ $ref->email }}</a></span>@endif
            </div>
            @if($ref->isRejected())
                <div style="color:#991b1b;font-size:.78rem;margin-top:.25rem;">Motivo: {{ $ref->rejection_reason }}</div>
                <button type="button" class="btn btn-sm btn-outline" style="margin-top:.35rem;" onclick="hdvRefAction('{{ route('rentals.references.restore', [$rental->id, $ref->id]) }}')">Restaurar</button>
            @else
                <details style="margin-top:.35rem;">
                    <summary style="cursor:pointer;font-size:.76rem;font-weight:700;color:var(--primary);">✏️ Editar datos</summary>
                    {!! $refForm($uid . '-e' . $ref->id, $ref->sort_order, $ref) !!}
                </details>
                <details style="margin-top:.35rem;">
                    <summary style="cursor:pointer;font-size:.76rem;font-weight:700;color:#b91c1c;">✕ Rechazar esta referencia</summary>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.35rem;align-items:center;">
                        <select id="refreason-{{ $ref->id }}" class="form-input" style="max-width:320px;">
                            @foreach($reasons as $rs)<option>{{ $rs }}</option>@endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-danger" onclick="hdvRefAction('{{ route('rentals.references.reject', [$rental->id, $ref->id]) }}', document.getElementById('refreason-{{ $ref->id }}').value)">Rechazar y pedir otra</button>
                    </div>
                </details>
            @endif
        </div>
    @empty
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:.6rem .8rem;font-size:.8rem;color:#92400e;">
            Aún no hay referencias capturadas. El inquilino las llena en su Portal ({{ $who === 'inquilino' ? 'Tus datos → Referencias personales' : 'Llenar los datos de su obligado' }}).
        </div>
    @endforelse
    @foreach($freeSlots as $slot)
        <details style="border:1px dashed var(--border);border-radius:8px;padding:.5rem .8rem;margin-bottom:.45rem;">
            <summary style="cursor:pointer;font-size:.8rem;font-weight:700;color:var(--primary);">＋ Capturar referencia {{ $slot }} (por teléfono, a nombre del cliente)</summary>
            {!! $refForm($uid . '-n' . $slot, $slot) !!}
        </details>
    @endforeach
    @if($validCount < 3 && $refs->count() > 0)
        <div style="font-size:.75rem;color:#92400e;">Faltan {{ 3 - $validCount }} referencia(s) válida(s) por capturar.</div>
    @endif
    <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;">Llámales y anota abajo el resultado. Rechaza las que no sirven (mamá, quien vive en la misma casa…): se le avisa al inquilino para que dé otra.</div>
</div>
@once
<script>
    // Crea un formulario aparte (esta sección está dentro de otro <form>) y lo envía.
    function hdvRefAction(url, reason) {
        var f = document.createElement('form'); f.method = 'POST'; f.action = url;
        var t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = '{{ csrf_token() }}'; f.appendChild(t);
        if (reason !== undefined) { var r = document.createElement('input'); r.type = 'hidden'; r.name = 'reason'; r.value = reason; f.appendChild(r); }
        document.body.appendChild(f); f.submit();
    }
    // Alta/edición de una referencia desde el CRM: lee los campos del contenedor y envía un formulario aparte.
    function hdvRefSave(url, boxId, who, slot) {
        var box = document.getElementById(boxId), f = document.createElement('form'); f.method = 'POST'; f.action = url;
        var add = function (n, v) { var i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; f.appendChild(i); };
        add('_token', '{{ csrf_token() }}'); add('who', who); add('slot', slot);
        box.querySelectorAll('[data-f]').forEach(function (el) { add(el.getAttribute('data-f'), el.value); });
        if (!f.querySelector('[name=name]').value.trim()) { alert('Escribe el nombre de la referencia.'); return; }
        document.body.appendChild(f); f.submit();
    }
</script>
@endonce
