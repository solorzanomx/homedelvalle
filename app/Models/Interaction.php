<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = [
        'client_id', 'form_submission_id', 'property_id', 'valuation_id', 'user_id', 'type', 'description',
        'scheduled_at', 'completed_at',
        'visit_token', 'confirmed_at', 'reminder_sent_at',
        'reschedule_requested_at', 'reschedule_message', 'send_confirmation_email',
        'visitor_reaction', 'visitor_comment', 'feedback_submitted_at',
        'price_perception', 'advisor_rating',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'             => 'datetime',
            'completed_at'             => 'datetime',
            'confirmed_at'             => 'datetime',
            'reminder_sent_at'         => 'datetime',
            'reschedule_requested_at'  => 'datetime',
            'send_confirmation_email'  => 'boolean',
            'feedback_submitted_at'    => 'datetime',
            'visitor_reaction'         => 'string',
            'price_perception'         => 'string',
            'advisor_rating'           => 'integer',
        ];
    }

    public function isVisit(): bool
    {
        return $this->type === 'visit';
    }

    /** Nombre del cliente si ya existe, o del lead (form_submission) si aun no se convierte. */
    public function contactName(): ?string
    {
        return $this->client?->name ?? $this->formSubmission?->full_name;
    }

    public function contactEmail(): ?string
    {
        return $this->client?->email ?? $this->formSubmission?->email;
    }

    public function contactPhone(): ?string
    {
        return $this->client?->phone ?? $this->formSubmission?->phone;
    }

    /**
     * Mensaje de confirmación de visita listo para WhatsApp — mismo dato
     * que lleva el correo (CitaMail/RecordatorioCitaMail), pero como texto
     * plano para el link wa.me. No hay integración real de WhatsApp Business
     * conectada (App\Services\WhatsAppService es un stub sin proveedor
     * configurado) — este es el mismo patrón "abre wa.me con el mensaje
     * precargado, el broker le da enviar" que ya se usa en Leads.
     */
    public function whatsappConfirmationMessage(): string
    {
        $nombre    = explode(' ', trim($this->contactName() ?? ''))[0] ?: 'Hola';
        $prop      = $this->property;
        $direccion = $prop?->address
            ? $prop->address . ($prop->colony ? ', ' . $prop->colony : '')
            : 'la propiedad';
        $fecha = $this->scheduled_at
            ? $this->scheduled_at->locale('es')->isoFormat('dddd D [de] MMMM [a las] h:mm A')
            : 'la fecha que acordamos';
        $asesor     = $this->user?->name ? " Te acompaña {$this->user->name}." : '';
        $confirmUrl = url("/visit/{$this->visit_token}/confirm");

        return "Hola {$nombre}, soy de Home del Valle. Tu visita quedó agendada para el {$fecha} en {$direccion}.{$asesor} "
            . "Confirma tu asistencia aquí: {$confirmUrl}";
    }

    public function whatsappConfirmationUrl(): ?string
    {
        $phone = $this->contactPhone();
        if (!$phone || !$this->visit_token) {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) < 10) {
            return null;
        }

        return 'https://wa.me/' . $digits . '?text=' . urlencode($this->whatsappConfirmationMessage());
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function formSubmission() { return $this->belongsTo(FormSubmission::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function valuation() { return $this->belongsTo(PropertyValuation::class, 'valuation_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
