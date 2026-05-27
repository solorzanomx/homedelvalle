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

    {{-- Comisión --}}
    <div>
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
    </div>

    {{-- Precio sugerido --}}
    <div>
      <label style="display:block;font-size:.82rem;font-weight:600;margin-bottom:.4rem;">
        Precio de referencia sugerido
        <span style="font-weight:400;color:var(--text-muted);font-size:.75rem;">(aparece en la presentación)</span>
      </label>
      <input wire:model.live.debounce.600ms="price_suggested"
             type="text" placeholder="Ej. $3,500,000 MXN"
             style="width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:var(--radius);font-family:inherit;font-size:.88rem;">
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
      Sin email registrado. <a href="{{ route('admin.clients.edit', $captacion->client_id) }}" style="color:#92400e;font-weight:600;">Agregar email →</a>
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

{{-- Script para recargar el iframe cuando Livewire actualiza la URL --}}
<script>
window.addEventListener('pdfUrlUpdated', function(e) {
    const frame = document.getElementById('presentation-pdf-frame');
    if (frame) frame.src = e.detail.url;
});
</script>

</div>
