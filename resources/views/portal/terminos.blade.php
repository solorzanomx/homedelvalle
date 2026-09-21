@extends('layouts.portal')
@section('title', 'Documentos legales')

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
.terminos-doc-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}
.terminos-doc {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    max-height: 380px;
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
        <h2>Antes de continuar</h2>
        <p>Lee y acepta los siguientes documentos para usar tu portal.</p>
    </div>

    @foreach($pendingDocs as $doc)
    <div class="terminos-doc-title">{{ $doc->title }}</div>
    <div class="terminos-doc">
        {!! $doc->currentVersion->content !!}
    </div>
    @endforeach

    <form method="POST" action="{{ route('portal.terminos.aceptar') }}" id="acceptForm">
        @csrf
        <div class="terminos-accept">
            <label>
                <input type="checkbox" id="acceptCheck" required onchange="document.getElementById('acceptBtn').disabled = !this.checked;">
                He leído y acepto {{ $pendingDocs->count() > 1 ? 'estos documentos' : 'este documento' }} de Home del Valle Bienes Raíces.
            </label>
            <button type="submit" id="acceptBtn" class="btn btn-primary" disabled>
                Continuar al portal →
            </button>
        </div>
    </form>
</div>
@endsection
