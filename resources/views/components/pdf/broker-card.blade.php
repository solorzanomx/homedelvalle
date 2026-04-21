{{-- Componente: Tarjeta del Broker --}}
{{-- Uso: @include('components.pdf.broker-card', ['broker' => $broker]) --}}

<div class="broker-section">
    <div class="broker-title">Asesor Inmobiliario</div>
    <div class="broker-card">
        <div class="broker-photo">
            @if($broker->profile_photo_url && file_exists(public_path('storage/' . $broker->profile_photo_url)))
                <img src="{{ public_path('storage/' . $broker->profile_photo_url) }}" alt="{{ $broker->name }}">
            @elseif($broker->photo_path && file_exists(public_path('storage/' . $broker->photo_path)))
                <img src="{{ public_path('storage/' . $broker->photo_path) }}" alt="{{ $broker->name }}">
            @else
                <div class="broker-photo-placeholder">
                    👤
                </div>
            @endif
        </div>
        <div class="broker-info">
            <div class="broker-name">
                {{ $broker->name ?? 'Asesor' }}
                @if($broker->last_name) {{ $broker->last_name }} @endif
            </div>
            @if($broker->position || $broker->role)
                <div class="broker-position">
                    {{ $broker->position ?? $broker->role ?? 'Agente Inmobiliario' }}
                </div>
            @endif
            <div class="broker-contact">
                @if($broker->phone || $broker->mobile)
                    <div class="broker-contact-item">
                        <span class="broker-contact-label">📱</span>
                        {{ $broker->phone ?? $broker->mobile }}
                    </div>
                @endif
                @if($broker->email)
                    <div class="broker-contact-item">
                        <span class="broker-contact-label">✉️</span>
                        {{ $broker->email }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
