<div>

{{-- ── Mensajes ─────────────────────────────────────────────────────────────── --}}
@if($successMsg)
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:.83rem;font-weight:600;color:#166534;">✓ {{ $successMsg }}</span>
    <button wire:click="clearMsg" style="background:none;border:none;cursor:pointer;color:#6ee7b7;font-size:1.1rem;">&times;</button>
</div>
@endif
@if($errorMsg)
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:9px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:.83rem;font-weight:600;color:#991b1b;">⚠ {{ $errorMsg }}</span>
    <button wire:click="clearMsg" style="background:none;border:none;cursor:pointer;color:#fca5a5;font-size:1.1rem;">&times;</button>
</div>
@endif

{{-- ── Barra de acciones rápidas ───────────────────────────────────────────── --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:.75rem 1.1rem;margin-bottom:1.25rem;display:flex;gap:.65rem;flex-wrap:wrap;align-items:center;">
    <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-right:.25rem;">Acciones:</span>

    <button wire:click="$set('activePanel', 'stage')"
            style="padding:.35rem .85rem;font-size:.78rem;font-weight:600;background:#eff6ff;color:#1D4ED8;border:1px solid #bfdbfe;border-radius:7px;cursor:pointer;">
        ⇄ Cambiar etapa
    </button>
    <button wire:click="$set('activePanel', 'nota')"
            style="padding:.35rem .85rem;font-size:.78rem;font-weight:600;background:#f8fafc;color:#374151;border:1px solid #e2e8f0;border-radius:7px;cursor:pointer;">
        📝 Agregar nota
    </button>
    @if($hasPayments)
    <button wire:click="generatePayments"
            style="padding:.35rem .85rem;font-size:.78rem;font-weight:600;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:7px;cursor:pointer;">
        💳 Generar pagos
    </button>
    @endif
    <button wire:click="$set('activePanel', 'renovacion')"
            style="padding:.35rem .85rem;font-size:.78rem;font-weight:600;background:#fffbeb;color:#92400e;border:1px solid #fde68a;border-radius:7px;cursor:pointer;">
        🔄 Renovar contrato
    </button>
    <button wire:click="$set('activePanel', 'moveout')"
            style="padding:.35rem .85rem;font-size:.78rem;font-weight:600;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:7px;cursor:pointer;">
        📦 Move-out
    </button>
</div>

{{-- ── Paneles de acción ───────────────────────────────────────────────────── --}}

@if($activePanel === 'stage')
<div style="background:#fff;border:1px solid #bfdbfe;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <span style="font-weight:700;font-size:.9rem;">⇄ Cambiar etapa</span>
        <button wire:click="$set('activePanel', '')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.2rem;">&times;</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:.85rem;">
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Nueva etapa</label>
            <select wire:model="newStage" style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
                @foreach($stages as $key => $lbl)
                <option value="{{ $key }}">{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Nota (opcional)</label>
            <input wire:model="stageNote" type="text" placeholder="Motivo del cambio..."
                   style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
        </div>
    </div>
    <button wire:click="changeStage"
            style="padding:.4rem 1rem;font-size:.82rem;font-weight:600;background:#1D4ED8;color:#fff;border:none;border-radius:7px;cursor:pointer;">
        Guardar cambio
    </button>
</div>
@endif

@if($activePanel === 'nota')
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <span style="font-weight:700;font-size:.9rem;">📝 Agregar nota</span>
        <button wire:click="$set('activePanel', '')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.2rem;">&times;</button>
    </div>
    <textarea wire:model="notaText" rows="3" placeholder="Escribe tu nota aquí..."
              style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.5rem .75rem;font-size:.82rem;resize:vertical;margin-bottom:.75rem;"></textarea>
    @error('notaText')<p style="font-size:.72rem;color:#ef4444;margin-bottom:.5rem;">{{ $message }}</p>@enderror
    <button wire:click="addNota"
            style="padding:.4rem 1rem;font-size:.82rem;font-weight:600;background:#0f172a;color:#fff;border:none;border-radius:7px;cursor:pointer;">
        Guardar nota
    </button>
</div>
@endif

@if($activePanel === 'renovacion')
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <span style="font-weight:700;font-size:.9rem;">🔄 Renovar contrato</span>
        <button wire:click="$set('activePanel', '')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.2rem;">&times;</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.85rem;margin-bottom:.85rem;">
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Nueva fecha de fin *</label>
            <input wire:model="newEndDate" type="date"
                   style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
            @error('newEndDate')<p style="font-size:.68rem;color:#ef4444;margin-top:.2rem;">{{ $message }}</p>@enderror
        </div>
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Nueva renta/mes *</label>
            <input wire:model="newRent" type="number" placeholder="{{ $rental->monthly_rent }}"
                   style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
            @error('newRent')<p style="font-size:.68rem;color:#ef4444;margin-top:.2rem;">{{ $message }}</p>@enderror
        </div>
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Nota</label>
            <input wire:model="renewalNote" type="text" placeholder="Ej: Incremento anual"
                   style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
        </div>
    </div>
    <button wire:click="renovar"
            style="padding:.4rem 1rem;font-size:.82rem;font-weight:600;background:#d97706;color:#fff;border:none;border-radius:7px;cursor:pointer;">
        Confirmar renovación
    </button>
</div>
@endif

@if($activePanel === 'moveout')
<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <span style="font-weight:700;font-size:.9rem;">📦 Programar Move-out</span>
        <button wire:click="$set('activePanel', '')" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:1.2rem;">&times;</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:.85rem;margin-bottom:.85rem;">
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Fecha de salida *</label>
            <input wire:model="moveOutDate" type="date"
                   style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
            @error('moveOutDate')<p style="font-size:.68rem;color:#ef4444;margin-top:.2rem;">{{ $message }}</p>@enderror
        </div>
        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;color:#64748b;margin-bottom:.3rem;">Notas</label>
            <input wire:model="moveOutNote" type="text" placeholder="Motivo, condición del inmueble..."
                   style="width:100%;border:1px solid #e2e8f0;border-radius:7px;padding:.4rem .7rem;font-size:.82rem;">
        </div>
    </div>
    <button wire:click="scheduleMoveOut"
            style="padding:.4rem 1rem;font-size:.82rem;font-weight:600;background:#dc2626;color:#fff;border:none;border-radius:7px;cursor:pointer;">
        Programar salida
    </button>
</div>
@endif

{{-- ── Grid principal ──────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;">

    {{-- IZQUIERDA --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Contrato --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.75rem 1.1rem;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.82rem;font-weight:700;">📄 Contrato</span>
                <span style="font-size:.7rem;font-weight:600;padding:.2rem .65rem;border-radius:9999px;background:{{ $rental->stage_color ?? '#e2e8f0' }}20;color:{{ $rental->stage_color ?? '#64748b' }};">
                    {{ $rental->stage_label ?? $rental->stage }}
                </span>
            </div>
            <div style="padding:.85rem 1.1rem;display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem 1.25rem;">
                @foreach([
                    ['Renta/mes',  $rental->monthly_rent ? '$'.number_format($rental->monthly_rent) : '—'],
                    ['Depósito',   $rental->deposit_amount ? '$'.number_format($rental->deposit_amount) : '—'],
                    ['Garantía',   $rental->guarantee_type_label ?? $rental->guarantee_type ?? '—'],
                    ['Día de pago',$rental->payment_day ? 'Día '.$rental->payment_day : '—'],
                    ['Inicio',     $rental->lease_start_date?->format('d/m/Y') ?? '—'],
                    ['Vencimiento',$rental->lease_end_date?->format('d/m/Y') ?? '—'],
                    ['Duración',   $rental->lease_duration_months ? $rental->lease_duration_months.' meses' : '—'],
                    ['Moneda',     $rental->currency ?? 'MXN'],
                ] as [$lbl, $val])
                <div>
                    <p style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.2rem;">{{ $lbl }}</p>
                    <p style="font-size:.83rem;font-weight:600;color:#0f172a;">{{ $val }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Pagos --}}
        @if($hasPayments)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.75rem 1.1rem;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.82rem;font-weight:700;">💳 Seguimiento de pagos</span>
                <span style="font-size:.72rem;color:#64748b;">{{ $payments->count() }} períodos</span>
            </div>
            @if($payments->isEmpty())
            <div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:.82rem;">
                Sin pagos generados. Haz clic en "Generar pagos" para crear el calendario.
            </div>
            @else
            <div style="max-height:340px;overflow-y:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.78rem;">
                    <thead>
                        <tr style="background:#f8fafc;position:sticky;top:0;">
                            <th style="padding:.5rem 1rem;text-align:left;font-size:.62rem;font-weight:700;text-transform:uppercase;color:#64748b;">Período</th>
                            <th style="padding:.5rem 1rem;text-align:right;font-size:.62rem;font-weight:700;text-transform:uppercase;color:#64748b;">Monto</th>
                            <th style="padding:.5rem 1rem;text-align:center;font-size:.62rem;font-weight:700;text-transform:uppercase;color:#64748b;">Estado</th>
                            <th style="padding:.5rem 1rem;text-align:center;font-size:.62rem;font-weight:700;text-transform:uppercase;color:#64748b;">Fecha pago</th>
                            <th style="padding:.5rem 1rem;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $pmt)
                        @php
                            $isCurrent = $pmt->period->format('Y-m') === now()->format('Y-m');
                            $isPast    = $pmt->period->lt(now()->startOfMonth());
                            $rowBg     = $isCurrent ? '#eff6ff' : '';
                        @endphp
                        <tr style="border-top:1px solid #f1f5f9;background:{{ $rowBg }};">
                            <td style="padding:.5rem 1rem;font-weight:{{ $isCurrent ? '700' : '500' }};color:#0f172a;">
                                {{ ucfirst($pmt->period->locale('es')->isoFormat('MMM YYYY')) }}
                                @if($isCurrent)<span style="font-size:.6rem;background:#dbeafe;color:#1D4ED8;padding:.1rem .3rem;border-radius:4px;margin-left:.3rem;">HOY</span>@endif
                            </td>
                            <td style="padding:.5rem 1rem;text-align:right;font-weight:600;color:#0f172a;">${{ number_format($pmt->amount) }}</td>
                            <td style="padding:.5rem 1rem;text-align:center;">
                                <span style="font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;background:{{ $pmt->status_color }}20;color:{{ $pmt->status_color }};">
                                    {{ $pmt->status_label }}
                                </span>
                            </td>
                            <td style="padding:.5rem 1rem;text-align:center;font-size:.75rem;color:#64748b;">
                                {{ $pmt->paid_at?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td style="padding:.5rem 1rem;text-align:right;">
                                @if(in_array($pmt->status, ['pending','late']))
                                <div style="display:flex;gap:.3rem;justify-content:flex-end;">
                                    <button wire:click="markPaid({{ $pmt->id }})"
                                            style="padding:.2rem .55rem;font-size:.65rem;font-weight:700;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:5px;cursor:pointer;">
                                        ✓ Pagado
                                    </button>
                                    @if($pmt->status === 'pending')
                                    <button wire:click="markLate({{ $pmt->id }})"
                                            style="padding:.2rem .55rem;font-size:.65rem;font-weight:700;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:5px;cursor:pointer;">
                                        Atrasado
                                    </button>
                                    @endif
                                </div>
                                @endif
                            </td>
                        </tr>
                        @if($activePanel === 'pago' && $paymentId === $pmt->id)
                        <tr style="background:#f0fdf4;">
                            <td colspan="5" style="padding:.75rem 1rem;">
                                <div style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
                                    <div>
                                        <label style="display:block;font-size:.65rem;font-weight:700;color:#64748b;margin-bottom:.2rem;">Fecha de pago</label>
                                        <input wire:model="paidAt" type="date"
                                               style="border:1px solid #e2e8f0;border-radius:6px;padding:.3rem .6rem;font-size:.78rem;">
                                    </div>
                                    <div style="flex:1;">
                                        <label style="display:block;font-size:.65rem;font-weight:700;color:#64748b;margin-bottom:.2rem;">Nota</label>
                                        <input wire:model="paymentNote" type="text" placeholder="Referencia, banco..."
                                               style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:.3rem .6rem;font-size:.78rem;">
                                    </div>
                                    <button wire:click="markPaid({{ $pmt->id }})"
                                            style="padding:.35rem .85rem;font-size:.78rem;font-weight:600;background:#10b981;color:#fff;border:none;border-radius:6px;cursor:pointer;">
                                        Confirmar
                                    </button>
                                    <button wire:click="$set('activePanel','')"
                                            style="padding:.35rem .65rem;font-size:.78rem;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer;">
                                        ✕
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- Historial / Notas --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.75rem 1.1rem;border-bottom:1px solid #e2e8f0;">
                <span style="font-size:.82rem;font-weight:700;">🕐 Historial y notas</span>
            </div>
            <div style="max-height:380px;overflow-y:auto;">
                @forelse($rental->stageLogs->sortByDesc('created_at') as $log)
                @php
                    $isNota   = str_starts_with($log->notes ?? '', '📝');
                    $isRenov  = str_starts_with($log->notes ?? '', '🔄');
                    $isMoveout= str_starts_with($log->notes ?? '', '📦');
                    $dotColor = $isNota ? '#8b5cf6' : ($isRenov ? '#f59e0b' : ($isMoveout ? '#ef4444' : '#3B82C4'));
                @endphp
                <div style="display:flex;gap:.75rem;padding:.65rem 1.1rem;border-bottom:1px solid #f8fafc;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $dotColor }};flex-shrink:0;margin-top:.4rem;"></span>
                    <div style="flex:1;min-width:0;">
                        @if($log->from_stage !== $log->to_stage)
                        <p style="font-size:.75rem;font-weight:600;color:#0f172a;">
                            {{ \App\Models\RentalProcess::STAGES[$log->from_stage] ?? $log->from_stage }}
                            → {{ \App\Models\RentalProcess::STAGES[$log->to_stage] ?? $log->to_stage }}
                        </p>
                        @endif
                        @if($log->notes)
                        <p style="font-size:.75rem;color:#374151;margin-top:.1rem;">{{ $log->notes }}</p>
                        @endif
                    </div>
                    <div style="text-align:right;flex-shrink:0;">
                        <p style="font-size:.65rem;color:#94a3b8;">{{ $log->user?->name ?? '—' }}</p>
                        <p style="font-size:.62rem;color:#cbd5e1;">{{ $log->created_at->format('d/m H:i') }}</p>
                    </div>
                </div>
                @empty
                <p style="padding:1.25rem;font-size:.8rem;color:#94a3b8;text-align:center;">Sin historial aún.</p>
                @endforelse
            </div>
        </div>

    </div>{{-- /izquierda --}}

    {{-- DERECHA --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Propietario --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.65rem 1rem;border-bottom:1px solid #e2e8f0;font-size:.78rem;font-weight:700;">🏠 Propietario</div>
            <div style="padding:.85rem 1rem;">
                @if($rental->ownerClient)
                <p style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $rental->ownerClient->name }}</p>
                <p style="font-size:.75rem;color:#64748b;margin-top:.2rem;">{{ $rental->ownerClient->email }}</p>
                <p style="font-size:.75rem;color:#64748b;">{{ $rental->ownerClient->phone }}</p>
                <a href="{{ route('clients.show', $rental->owner_client_id) }}" style="display:inline-block;margin-top:.5rem;font-size:.72rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver perfil →</a>
                @else <p style="font-size:.8rem;color:#94a3b8;">Sin propietario asignado</p> @endif
            </div>
        </div>

        {{-- Inquilino --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.65rem 1rem;border-bottom:1px solid #e2e8f0;font-size:.78rem;font-weight:700;">🔑 Inquilino</div>
            <div style="padding:.85rem 1rem;">
                @if($rental->tenantClient)
                <p style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $rental->tenantClient->name }}</p>
                <p style="font-size:.75rem;color:#64748b;margin-top:.2rem;">{{ $rental->tenantClient->email }}</p>
                <p style="font-size:.75rem;color:#64748b;">{{ $rental->tenantClient->phone }}</p>
                <a href="{{ route('clients.show', $rental->tenant_client_id) }}" style="display:inline-block;margin-top:.5rem;font-size:.72rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver perfil →</a>
                @else <p style="font-size:.8rem;color:#94a3b8;">Sin inquilino asignado</p> @endif
            </div>
        </div>

        {{-- Inmueble --}}
        @if($rental->property)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.65rem 1rem;border-bottom:1px solid #e2e8f0;font-size:.78rem;font-weight:700;">📍 Inmueble</div>
            <div style="padding:.85rem 1rem;">
                <p style="font-size:.82rem;font-weight:600;color:#0f172a;">{{ $rental->property->address }}</p>
                @if($rental->property->colony)<p style="font-size:.72rem;color:#64748b;margin-top:.15rem;">{{ $rental->property->colony }}</p>@endif
                <a href="{{ route('admin.properties.show', $rental->property_id) }}" style="display:inline-block;margin-top:.5rem;font-size:.72rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver ficha →</a>
            </div>
        </div>
        @endif

        {{-- Póliza Jurídica --}}
        @if($rental->polizaJuridica ?? false)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.65rem 1rem;border-bottom:1px solid #e2e8f0;font-size:.78rem;font-weight:700;">⚖️ Póliza Jurídica</div>
            <div style="padding:.85rem 1rem;">
                <p style="font-size:.78rem;color:#0f172a;">{{ $rental->polizaJuridica->provider ?? 'Activa' }}</p>
                @if($rental->polizaJuridica->expiry_date)
                <p style="font-size:.72rem;color:#64748b;margin-top:.2rem;">Vence: {{ $rental->polizaJuridica->expiry_date->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Documentos --}}
        @if($rental->documents->count())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
            <div style="padding:.65rem 1rem;border-bottom:1px solid #e2e8f0;font-size:.78rem;font-weight:700;">📎 Documentos ({{ $rental->documents->count() }})</div>
            <div>
                @foreach($rental->documents->take(6) as $doc)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem 1rem;border-bottom:1px solid #f8fafc;">
                    <div style="min-width:0;flex:1;">
                        <p style="font-size:.75rem;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->label ?? $doc->file_name }}</p>
                        <p style="font-size:.65rem;color:#64748b;">{{ $doc->category_label }}</p>
                    </div>
                    <a href="{{ route('documents.download', $doc->id) }}" style="font-size:.7rem;color:#1D4ED8;font-weight:600;text-decoration:none;margin-left:.5rem;flex-shrink:0;">↓</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /derecha --}}
</div>

</div>
