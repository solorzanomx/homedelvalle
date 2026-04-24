@extends('layouts.portal')
@section('title', 'Aviso de Privacidad')

@section('styles')
<style>
.terminos-wrap {
    max-width: 720px;
    margin: 2rem auto;
}
.terminos-header {
    text-align: center;
    margin-bottom: 2rem;
}
.terminos-header h2 {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.4rem;
}
.terminos-header p {
    color: var(--text-muted);
    font-size: 0.88rem;
}
.terminos-doc {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    max-height: 460px;
    overflow-y: auto;
    font-size: 0.85rem;
    line-height: 1.7;
    margin-bottom: 1.5rem;
}
.terminos-accept {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.terminos-accept label {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
}
.terminos-accept input[type="checkbox"] {
    width: 17px;
    height: 17px;
    cursor: pointer;
    accent-color: var(--primary);
}
</style>
@endsection

@section('content')
<div class="terminos-wrap">
    <div class="terminos-header">
        <h2>Aviso de Privacidad</h2>
        <p>Antes de continuar, lee y acepta nuestro aviso de privacidad.</p>
    </div>

    <div class="terminos-doc">
        @if($aviso && $aviso->currentVersion)
            {!! $aviso->currentVersion->content !!}
        @else
            <p style="text-align:center; color:var(--text-muted); padding:2rem 0;">
                El aviso de privacidad aún no está disponible. Contacta a tu asesor.
            </p>
        @endif
    </div>

    @if($aviso && $aviso->currentVersion)
    <form method="POST" action="{{ route('portal.terminos.aceptar') }}" id="acceptForm">
        @csrf
        <div class="terminos-accept">
            <label>
                <input type="checkbox" id="acceptCheck" required onchange="document.getElementById('acceptBtn').disabled = !this.checked;">
                He leído y acepto el Aviso de Privacidad de Home del Valle Bienes Raíces.
            </label>
            <button type="submit" id="acceptBtn" class="btn btn-primary" disabled>
                Continuar al portal →
            </button>
        </div>
    </form>
    @else
    <div style="text-align:center; padding:1rem;">
        <a href="{{ route('portal.dashboard') }}" class="btn btn-outline">Continuar al portal</a>
    </div>
    @endif
</div>
@endsection
