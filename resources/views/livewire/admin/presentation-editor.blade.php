<div>

{{-- ── Variables editables ──────────────────────────────────────────────── --}}
<div class="card" style="margin-bottom:1rem;">
  <div class="card-header" style="justify-content:space-between;">
    <h3>Variables de la presentación</h3>
    <span wire:loading style="font-size:.75rem;color:var(--text-muted);">
      Actualizando...
    </span>
  </div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">

    {{-- Comisión: % para venta, meses para renta --}}
    <div>
      @if($this->isRenta())
        {{-- RENTA: selector de meses --}}
        <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
          Comisión propuesta
          <span style="font-weight:400;color:var(--text-muted);font-size:.75rem;">(meses de renta)</span>
        </label>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
          @foreach([0.5 => 'Medio mes', 1 => '1 mes', 1.5 => '1.5 meses', 2 => '2 meses'] as $val => $lbl)
          <label style="flex:1;min-width:80px;cursor:pointer;">
            <input wire:model.live="commission_pct" type="radio" value="{{ $val }}" style="margin-right:.3rem;">
            <span style="font-size:.82rem;">{{ $lbl }}</span>
          </label>
          @endforeach
        </div>
        <p style="font-size:.72rem;color:var(--text-muted);margin-top:.35rem;">
          Seleccionado: <strong style="color:var(--success);">
            @if((float)$commission_pct == 0.5) Medio mes de renta
            @elseif((float)$commission_pct == 1) 1 mes de renta
            @else {{ $commission_pct }} meses de renta
            @endif
          </strong>
        </p>
      @else
        {{-- VENTA: slider de porcentaje --}}
        <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
          Comisión propuesta (%)
        </label>
        <div style="display:flex;align-items:center;gap:.75rem;">
          <input wire:model.live.debounce.600ms="commission_pct"
                 type="number" min="0" max="100" step="0.5"
                 style="width:90px;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.9rem;font-weight:700;text-align:center;">
          <input wire:model.live.debounce.600ms="commission_pct"
                 type="range" min="0" max="10" step="0.5"
                 style="flex:1;accent-color:var(--primary);">
          <span style="font-size:1.1rem;font-weight:800;color:var(--success);min-width:40px;text-align:right;">{{ $commission_pct }}%</span>
        </div>
      @endif
    </div>

    {{-- Precio sugerido + referencia de mercado --}}
    <div>
      <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
        {{ $this->isRenta() ? 'Renta de referencia sugerida' : 'Precio de referencia sugerido' }}
        <span style="font-weight:400;color:var(--text-muted);font-size:.75rem;">(aparece en la presentación)</span>
      </label>
      <input wire:model.live.debounce.600ms="price_suggested"
             type="text"
             placeholder="{{ $this->isRenta() ? 'Ej. $28,000 MXN/mes' : 'Ej. $3,500,000 MXN' }}"
             style="width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">

      {{-- Badge de mercado si hay datos del observatorio --}}
      @if(isset($marketSnapshot) && $marketSnapshot)
      @php
        $isRent = $marketSnapshot->operation_type === 'rent';
        $unit   = $isRent ? '/m²/mes' : '/m²';
        $confColor = match($marketSnapshot->confidence) { 'high' => '#059669', 'medium' => '#d97706', default => '#94a3b8' };
      @endphp
      <div style="margin-top:.45rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:.45rem .75rem;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
        <span style="font-size:.75rem;color:#166534;">
          📊 Mercado zona:
          <strong>${{ number_format($marketSnapshot->price_m2_low, 0) }}–${{ number_format($marketSnapshot->price_m2_high, 0) }}{{ $unit }}</strong>
          · {{ $marketSnapshot->colonia->name ?? '' }}
        </span>
        <span style="font-size:.68rem;background:{{ $confColor }}20;color:{{ $confColor }};padding:1px 7px;border-radius:4px;font-weight:600;">
          {{ $marketSnapshot->confidence_label }}
        </span>
      </div>
      @endif
    </div>

    {{-- Plan de marketing --}}
    <div>
      <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
        Plan de marketing
        <span style="font-weight:400;color:var(--text-muted);font-size:.75rem;">(editable · aparece en pág. 5)</span>
      </label>
      <textarea wire:model.live.debounce.600ms="marketing_plan"
                rows="6"
                style="width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.83rem;line-height:1.6;resize:vertical;">{{ $marketing_plan }}</textarea>
    </div>

    <button wire:click="regenerate" wire:loading.attr="disabled" class="btn btn-outline btn-sm" style="align-self:flex-start;">
      <span wire:loading.remove wire:target="regenerate">
        <x-icon name="plus" class="w-3 h-3" />
        Regenerar ahora
      </span>
      <span wire:loading wire:target="regenerate">Regenerando...</span>
    </button>

  </div>
</div>

{{-- ── Envío --}}
<div class="card" style="margin-bottom:1rem;">
  <div class="card-header"><h3>Enviar</h3></div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">

  @if($captacion->status === 'declinado')
    {{-- Caso declinado: bloquear envíos --}}
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.9rem 1rem;text-align:center;">
      <div style="font-size:1.3rem;margin-bottom:.25rem;">&#10005;</div>
      <p style="font-size:.83rem;font-weight:700;color:#dc2626;margin:0 0 .2rem;">Caso declinado</p>
      <p style="font-size:.75rem;color:#991b1b;margin:0;">No se puede enviar la presentación a un caso declinado.</p>
      @if($captacion->declined_reason)
      <p style="font-size:.72rem;color:#b91c1c;margin:.5rem 0 0;font-style:italic;">"{{ Str::limit($captacion->declined_reason, 80) }}"</p>
      @endif
    </div>
    <a href="{{ route('admin.captaciones.presentation.admin.download', $captacion) }}"
       class="btn btn-outline" style="width:100%;justify-content:center;opacity:.7;">
      <x-icon name="arrow-right" class="w-4 h-4" />
      Descargar PDF (solo admin)
    </a>
  @else

    @if($captacion->client->email)
    <form method="POST" action="{{ route('admin.captaciones.presentation.send.email', $captacion) }}">
      @csrf
      <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;">Email del propietario</label>
      <input type="email" name="email" value="{{ $captacion->client->email }}"
             style="width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.85rem;margin-bottom:.5rem;">
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
        <x-icon name="send" class="w-4 h-4" />
        Enviar por email + PDF adjunto
      </button>
    </form>
    @else
    <div style="background:#fefce8;border:1px solid #fde047;border-radius:6px;padding:.6rem .9rem;font-size:.8rem;color:#713f12;">
      <x-icon name="triangle-alert" class="w-[13px] h-[13px]" style="display:inline;vertical-align:middle;" />
      Sin email registrado. <a href="{{ route('clients.edit', $captacion->client_id) }}" style="color:#92400e;font-weight:600;">Agregar email →</a>
    </div>
    @endif

    @php $waPhone = $captacion->client->whatsapp ?? $captacion->client->phone ?? ''; @endphp
    <form method="POST" action="{{ route('admin.captaciones.presentation.send.whatsapp', $captacion) }}">
      @csrf
      <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;">WhatsApp</label>
      <input type="tel" name="phone" value="{{ $waPhone }}" placeholder="55 1234 5678"
             style="width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.85rem;margin-bottom:.5rem;" required>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:#25D366;border-color:#25D366;">
        Abrir WhatsApp Desktop →
      </button>
    </form>

    <a href="{{ route('admin.captaciones.presentation.admin.download', $captacion) }}"
       class="btn btn-outline" style="width:100%;justify-content:center;">
      <x-icon name="arrow-right" class="w-4 h-4" />
      Descargar PDF
    </a>

    @if($latestToken)
    <a href="{{ route('presentation.public', $latestToken) }}" target="_blank"
       class="btn btn-outline btn-sm" style="width:100%;justify-content:center;font-size:.78rem;">
      <x-icon name="eye" class="w-3 h-3" />
      Ver como propietario
    </a>
    @endif

  @endif

  </div>
</div>

{{-- ── Tracking ─────────────────────────────────────────────────────────── --}}
@if($captacion->sends()->exists())
<div class="card">
  <div class="card-header"><h3>Actividad de envíos</h3></div>
  <div class="card-body" style="padding:0;">
    @foreach($captacion->sends()->latest()->get() as $s)
    <div style="padding:.7rem 1.25rem;border-bottom:1px solid var(--border);display:flex;flex-direction:column;gap:.25rem;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:.82rem;font-weight:600;">{{ $s->channel_label }}</span>
        <span style="font-size:.72rem;color:var(--text-muted);">{{ $s->sent_at->diffForHumans() }}</span>
      </div>
      <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
        @if($s->email_opened_at) <span style="background:#ecfdf5;color:#065f46;padding:1px 7px;border-radius:4px;font-size:.7rem;font-weight:600;">✓ Email abierto</span> @endif
        @if($s->pdf_viewed_at)   <span style="background:#ecfdf5;color:#065f46;padding:1px 7px;border-radius:4px;font-size:.7rem;font-weight:600;">✓ PDF visto ×{{ $s->pdf_view_count }}</span> @endif
        @if($s->pdf_downloaded_at) <span style="background:#ecfdf5;color:#065f46;padding:1px 7px;border-radius:4px;font-size:.7rem;font-weight:600;">✓ Descargado</span> @endif
        @if(!$s->email_opened_at && !$s->pdf_viewed_at) <span style="background:#fef3c7;color:#92400e;padding:1px 7px;border-radius:4px;font-size:.7rem;">Sin abrir</span> @endif
      </div>
    </div>
    @endforeach
  </div>
</div>
@endif


</div>
