{{-- Partial reutilizable para tablas de RentalProcess --}}
@if($rentas->isEmpty())
<div style="text-align:center;padding:3rem;color:#94a3b8;">
    <svg style="width:2.5rem;height:2.5rem;margin:0 auto .75rem;display:block;color:#cbd5e1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3H8l-1 4h10l-1-4z"/></svg>
    <p style="font-size:.85rem;">{{ $empty }}</p>
</div>
@else
<div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
        <thead>
            <tr style="background:#f8fafc;border-bottom:2px solid var(--border);">
                <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Inmueble</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Propietario</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Inquilino</th>
                <th style="padding:.65rem 1rem;text-align:right;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Renta</th>
                <th style="padding:.65rem 1rem;text-align:center;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Inicio</th>
                <th style="padding:.65rem 1rem;text-align:center;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Fin</th>
                <th style="padding:.65rem 1rem;text-align:center;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Etapa</th>
                <th style="padding:.65rem 1rem;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($rentas as $r)
            @php
                $daysLeft = $r->lease_end_date ? now()->diffInDays($r->lease_end_date, false) : null;
                $rowColor = '';
                if ($daysLeft !== null && $daysLeft <= 0) $rowColor = 'background:#fef2f2;';
                elseif ($daysLeft !== null && $daysLeft <= 30) $rowColor = 'background:#fffbeb;';
            @endphp
            <tr style="border-bottom:1px solid var(--border);{{ $rowColor }}" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ $rowColor }}'">
                <td style="padding:.65rem 1rem;">
                    <p style="font-weight:600;color:#0f172a;">{{ $r->property?->address ?? '—' }}</p>
                    @if($r->property?->colony)
                    <p style="font-size:.68rem;color:#64748b;">{{ $r->property->colony }}</p>
                    @endif
                </td>
                <td style="padding:.65rem 1rem;color:#374151;">{{ $r->ownerClient?->name ?? '—' }}</td>
                <td style="padding:.65rem 1rem;color:#374151;">{{ $r->tenantClient?->name ?? '—' }}</td>
                <td style="padding:.65rem 1rem;text-align:right;font-weight:600;color:#1D4ED8;">
                    @if($r->monthly_rent) ${{ number_format($r->monthly_rent) }} @else — @endif
                </td>
                <td style="padding:.65rem 1rem;text-align:center;color:#64748b;">
                    {{ $r->lease_start_date?->format('d/m/Y') ?? '—' }}
                </td>
                <td style="padding:.65rem 1rem;text-align:center;">
                    @if($r->lease_end_date)
                    <span style="color:{{ $daysLeft !== null && $daysLeft <= 30 ? '#f59e0b' : '#64748b' }};font-weight:{{ $daysLeft !== null && $daysLeft <= 30 ? '600' : '400' }};">
                        {{ $r->lease_end_date->format('d/m/Y') }}
                        @if($daysLeft !== null && $daysLeft > 0 && $daysLeft <= 60)
                        <span style="display:block;font-size:.65rem;color:#f59e0b;">{{ $daysLeft }} días</span>
                        @elseif($daysLeft !== null && $daysLeft <= 0)
                        <span style="display:block;font-size:.65rem;color:#ef4444;font-weight:700;">Vencido</span>
                        @endif
                    </span>
                    @else —
                    @endif
                </td>
                <td style="padding:.65rem 1rem;text-align:center;">
                    <span style="display:inline-block;padding:.2rem .6rem;border-radius:9999px;font-size:.65rem;font-weight:600;background:{{ $r->stage_color ?? '#e2e8f0' }}1a;color:{{ $r->stage_color ?? '#64748b' }};">
                        {{ $r->stage_label ?? $r->stage }}
                    </span>
                </td>
                <td style="padding:.65rem 1rem;text-align:right;">
                    <a href="{{ route('admin.rentas.gestion.show', $r->id) }}" style="font-size:.75rem;color:#1D4ED8;font-weight:600;text-decoration:none;">Ver →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
