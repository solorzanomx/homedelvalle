@extends('layouts.public')

@section('meta')
    <title>{{ $document->title }} - {{ $siteSettings->site_name ?? 'Homedelvalle' }}</title>
    @if($document->meta_description)
        <meta name="description" content="{{ $document->meta_description }}">
    @endif
@endsection

@section('content')
<section style="padding: 3rem 1rem 4rem; min-height: 60vh;">
    <div style="max-width: 800px; margin: 0 auto;">
        {{-- Header --}}
        <div style="margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <div style="margin-bottom: 0.75rem;">
                @php
                    $typeColors = [
                        'aviso_privacidad' => ['bg' => '#eef2ff', 'text' => '#3730a3'],
                        'terminos_condiciones' => ['bg' => '#f5f3ff', 'text' => '#5b21b6'],
                        'contrato' => ['bg' => '#fffbeb', 'text' => '#92400e'],
                        'otro' => ['bg' => '#ecfdf5', 'text' => '#065f46'],
                    ];
                    $color = $typeColors[$document->type] ?? $typeColors['otro'];
                @endphp
                <span style="display:inline-block; padding:0.2rem 0.65rem; font-size:0.75rem; font-weight:500; border-radius:20px; background:{{ $color['bg'] }}; color:{{ $color['text'] }};">
                    {{ \App\Models\LegalDocument::TYPES[$document->type] ?? $document->type }}
                </span>
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1.3; margin-bottom: 0.5rem;">
                {{ $document->title }}
            </h1>
            @if($document->meta_description)
                <p style="font-size: 1rem; color: #6b7280; margin-bottom: 0.75rem;">{{ $document->meta_description }}</p>
            @endif
            <p style="font-size: 0.85rem; color: #9ca3af;">
                Ultima actualizacion: {{ $document->currentVersion?->created_at?->translatedFormat('d \d\e F \d\e Y') ?? $document->updated_at->translatedFormat('d \d\e F \d\e Y') }}
                @if($document->currentVersion)
                    &middot; Version {{ $document->currentVersion->version_number }}
                @endif
            </p>
        </div>

        {{-- Content --}}
        <div class="legal-content" style="
            font-size: 1rem;
            line-height: 1.85;
            color: #374151;
            overflow-wrap: break-word;
        ">
            <style>
                .legal-content h1 { font-size: 1.75rem; font-weight: 700; color: #111827; margin-top: 2rem; margin-bottom: 1rem; }
                .legal-content h2 { font-size: 1.4rem; font-weight: 600; color: #1f2937; margin-top: 1.75rem; margin-bottom: 0.75rem; }
                .legal-content h3 { font-size: 1.15rem; font-weight: 600; color: #1f2937; margin-top: 1.5rem; margin-bottom: 0.5rem; }
                .legal-content p { margin-bottom: 1rem; }
                .legal-content ul, .legal-content ol { margin-bottom: 1rem; padding-left: 1.75rem; }
                .legal-content li { margin-bottom: 0.35rem; }
                .legal-content strong { font-weight: 600; color: #1f2937; }
                .legal-content a { color: var(--color-primary, #3B82C4); text-decoration: underline; }
                .legal-content a:hover { opacity: 0.8; }
                .legal-content table { border-collapse: collapse; width: 100%; margin-bottom: 1.25rem; }
                .legal-content table th, .legal-content table td { border: 1px solid #e5e7eb; padding: 0.6rem 0.85rem; font-size: 0.92rem; text-align: left; }
                .legal-content table th { background: #f9fafb; font-weight: 600; color: #1f2937; }
                .legal-content blockquote { border-left: 3px solid #d1d5db; margin: 1rem 0; padding: 0.75rem 1rem; color: #6b7280; background: #f9fafb; border-radius: 0 6px 6px 0; }
            </style>

            @if($document->currentVersion)
                {!! $document->currentVersion->content !!}
            @else
                <p style="text-align:center; color:#9ca3af; padding:3rem 0;">Este documento no tiene contenido disponible.</p>
            @endif
        </div>
    </div>
</section>
@endsection
