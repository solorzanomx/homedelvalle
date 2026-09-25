@extends('layouts.app-sidebar')
@section('title', 'Tarifario y cobertura')

@section('content')
<div style="margin-bottom:1rem;">
    <a href="{{ route('poliza-plans.index') }}" style="font-size:.78rem;">← Planes de póliza</a>
    <h1 style="font-size:1.25rem;font-weight:700;margin:.15rem 0 0;">Tarifario y cobertura</h1>
    <div style="font-size:.82rem;color:var(--text-muted);max-width:760px;">
        Lo que Previsión Legal cobra según la <strong>renta mensual</strong> y qué cubre cada plan. El propietario ve estos precios en su Portal y el sitio web los usa en su calculadora.
        Cuando el proveedor publique una hoja nueva (otro año o zona), actualiza los valores aquí.
    </div>
</div>

@if(! $sheet)
    <div class="card"><div class="card-body">No hay una hoja de servicios activa.</div></div>
@else
<form method="POST" action="{{ route('poliza-plans.tarifario.save') }}">
    @csrf
    <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="padding:1rem;">
        <h3 style="font-size:.9rem;margin:0 0 .6rem;">Hoja de servicios</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;">
            <div class="form-group"><label class="form-label">Nombre</label><input name="name" class="form-input" value="{{ old('name', $sheet->name) }}" required></div>
            <div class="form-group"><label class="form-label">Zona</label><input name="zone_label" class="form-input" value="{{ old('zone_label', $sheet->zone_label) }}"></div>
            <div class="form-group"><label class="form-label">Año</label><input name="valid_year" type="number" class="form-input" value="{{ old('valid_year', $sheet->valid_year) }}"></div>
            <div class="form-group"><label class="form-label">Gastos de emisión (MXN)</label><input name="emission_fee" type="number" step="0.01" min="0" class="form-input" value="{{ old('emission_fee', $sheet->emission_fee) }}" required></div>
        </div>
        <div style="font-size:.74rem;color:var(--text-muted);">Se cubren al iniciar el trámite; se acreditan al precio si la operación se concreta y no se reembolsan si no.</div>
    </div></div>

    <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="padding:1rem;overflow-x:auto;">
        <h3 style="font-size:.9rem;margin:0 0 .6rem;">Precio por rango de renta mensual</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;min-width:560px;">
            <thead><tr style="text-align:left;font-size:.7rem;text-transform:uppercase;color:var(--text-muted);">
                <th style="padding:.4rem;">Renta mensual</th>
                @foreach($plans as $pl)<th style="padding:.4rem;">{{ $pl->name }}</th>@endforeach
            </tr></thead>
            <tbody>
            @foreach($rates as $rowRates)
                @php $first = $rowRates->first(); $pct = $first->percent !== null; @endphp
                <tr style="border-top:1px solid var(--border);">
                    <td style="padding:.45rem .4rem;white-space:nowrap;">
                        @if($first->rent_up_to === null) Más de ${{ number_format($first->rent_over) }} <small style="color:var(--text-muted);">(% de la renta)</small>
                        @else De ${{ number_format($first->rent_over + 0.01, 2) }} a ${{ number_format($first->rent_up_to) }} @endif
                    </td>
                    @foreach($plans as $pl)
                        @php $rt = $rowRates->firstWhere('poliza_plan_id', $pl->id); @endphp
                        <td style="padding:.3rem .4rem;">
                            @if($rt)
                                @if($rt->percent !== null)
                                    <input name="rates[{{ $rt->id }}][percent]" type="number" step="0.01" min="0" max="100" class="form-input" style="max-width:110px;" value="{{ $rt->percent }}"> %
                                @else
                                    <input name="rates[{{ $rt->id }}][fixed_price]" type="number" step="0.01" min="0" class="form-input" style="max-width:130px;" value="{{ $rt->fixed_price }}">
                                @endif
                            @else — @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div></div>

    <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="padding:1rem;overflow-x:auto;">
        <h3 style="font-size:.9rem;margin:0 0 .6rem;">Matriz de cobertura (según la hoja)</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.83rem;min-width:560px;">
            <thead><tr style="text-align:left;font-size:.7rem;text-transform:uppercase;color:var(--text-muted);"><th style="padding:.4rem;">Concepto</th>@foreach($plans as $pl)<th style="padding:.4rem;text-align:center;">{{ $pl->name }}</th>@endforeach</tr></thead>
            <tbody>
            @foreach($coverages->groupBy('section') as $section => $items)
                <tr><td colspan="{{ 1 + $plans->count() }}" style="padding:.6rem .4rem .2rem;font-weight:700;background:var(--bg,#f8fafc);">{{ \App\Models\PolizaCoverage::SECTIONS[$section] ?? $section }}</td></tr>
                @foreach($items as $cov)
                <tr style="border-top:1px solid var(--border);">
                    <td style="padding:.4rem;">{{ $cov->label }}@if($cov->note) <small style="color:var(--text-muted);">* {{ $cov->note }}</small>@endif</td>
                    @foreach($plans as $pl)
                        @php $inc = (bool) optional($cov->plans->firstWhere('id', $pl->id))->pivot?->included; @endphp
                        <td style="text-align:center;"><input type="checkbox" name="cov[{{ $cov->id }}][{{ $pl->id }}]" value="1" {{ $inc ? 'checked' : '' }} style="width:18px;height:18px;"></td>
                    @endforeach
                </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div></div>

    <button class="btn btn-primary">Guardar tarifario y cobertura</button>
</form>
@endif
@endsection
