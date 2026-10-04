{{-- Co-arrendatario: cuando el contrato va a nombre de DOS personas (ej. una pareja que renta junta). A diferencia
     del obligado solidario, es SIEMPRE opcional y manual (no lo dispara ninguna ruta de garantía). Espera: $rental. --}}
@php
    $ctSvc = app(\App\Services\CoTenantService::class);
    $ctSt = $ctSvc->status($rental);
@endphp
<div class="card" style="margin-bottom:1rem;">
    <div class="card-body" style="padding:1rem;">
        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem;">
            <strong style="font-size:.9rem;">👫 Co-arrendatario</strong>
            <span style="font-size:.72rem;color:var(--text-muted);">(el contrato queda a nombre de dos personas)</span>
            @if($ctSt['complete'])<span class="badge badge-green">Completo y aprobado</span>
            @elseif($ctSt['registered'])<span class="badge badge-yellow">En proceso</span>@endif
        </div>
        @if($ctSt['registered'])
            <div style="font-size:.82rem;line-height:1.55;">
                <strong>{{ $ctSt['name'] }}</strong> · {{ $ctSt['client']->phone }}@if($ctSt['client']->email) · {{ $ctSt['client']->email }}@endif · <span style="color:var(--text-muted);">lo captura el inquilino titular desde su Portal</span><br>
                Datos {{ $ctSt['data_pct'] }}% · Documentos: {{ $ctSt['docs']['aprobado'] }} aprobados, {{ $ctSt['docs']['revision'] }} en revisión, {{ $ctSt['docs']['corregir'] }} por corregir, {{ $ctSt['docs']['falta'] }} por subir
                @if($ctSt['docs_missing'])<br><span style="color:#92400e;">Falta aprobar: {{ implode(' · ', $ctSt['docs_missing']) }}</span>@endif
                <div style="margin-top:.4rem;display:flex;gap:.4rem;flex-wrap:wrap;">
                    <a class="btn btn-sm btn-outline" href="{{ route('clients.show', $ctSt['client']->id) }}">Ver ficha</a>
                    <a class="btn btn-sm btn-outline" href="javascript:void(0)" onclick="switchTab('documents')">Ver sus documentos</a>
                </div>
            </div>
        @else
            <p style="font-size:.8rem;color:var(--text-muted);margin:.2rem 0 .6rem;">Úsalo cuando el contrato va a quedar a nombre de dos personas (ej. una pareja): la segunda persona pasa la misma investigación completa que el titular (no es un aval, es otro inquilino).</p>
        @endif
        <details style="margin-top:.5rem;" {{ $ctSt['registered'] ? '' : 'open' }}>
            <summary style="cursor:pointer;font-size:.8rem;font-weight:700;color:var(--primary);">{{ $ctSt['registered'] ? 'Cambiar a otra persona' : 'Registrar co-arrendatario' }}</summary>
            <form method="POST" action="{{ route('rentals.co-tenant.register', $rental->id) }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end;margin-top:.5rem;">@csrf
                <div class="form-group" style="margin:0;min-width:170px;"><label class="form-label" style="font-size:.72rem;">Nombre completo</label><input name="name" class="form-input" required></div>
                <div class="form-group" style="margin:0;min-width:150px;"><label class="form-label" style="font-size:.72rem;">Celular</label><input name="phone" type="tel" class="form-input" required></div>
                <div class="form-group" style="margin:0;min-width:190px;"><label class="form-label" style="font-size:.72rem;">Correo (opcional)</label><input name="email" type="email" class="form-input"></div>
                <div class="form-group" style="margin:0;min-width:150px;"><label class="form-label" style="font-size:.72rem;">Relación (opcional)</label><input name="relationship" class="form-input" placeholder="Ej. su pareja"></div>
                <button class="btn btn-sm btn-primary">{{ $ctSt['registered'] ? 'Guardar' : 'Registrar' }}</button>
            </form>
        </details>
    </div>
</div>
