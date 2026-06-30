<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AcuseEmailConfig extends Model {
    protected $fillable = [
        'form_type','subject','badge','titulo','bajada','nota',
        'cta1_label','cta1_type','cta1_url_static',
        'cta2_label','cta2_type','cta2_url_static',
        'paso1_icon','paso1_titulo','paso1_desc',
        'paso2_icon','paso2_titulo','paso2_desc',
        'paso3_icon','paso3_titulo','paso3_desc',
    ];

    public static function forType(string $formType): self {
        return static::firstOrNew(
            ['form_type' => $formType],
            [
                'subject'         => 'Recibimos tu mensaje · Home del Valle',
                'badge'           => 'Mensaje recibido',
                'titulo'          => '¡Recibimos tu mensaje!',
                'bajada'          => 'Un asesor te contactará en menos de 24 horas hábiles.',
                'nota'            => 'Pocos inmuebles. Más control. Mejores resultados.',
                'cta1_label'      => 'Ver propiedades',
                'cta1_type'       => 'static',
                'cta1_url_static' => 'https://homedelvalle.mx/propiedades',
                'paso1_icon'      => 'icon-mail.png',
                'paso1_titulo'    => 'Revisamos tu mensaje',
                'paso1_desc'      => 'Un asesor lee tu solicitud y prepara la mejor respuesta.',
                'paso2_icon'      => 'icon-chat.png',
                'paso2_titulo'    => 'Te contactamos',
                'paso2_desc'      => 'Por teléfono, email o WhatsApp según tu preferencia.',
                'paso3_icon'      => 'icon-homestep.png',
                'paso3_titulo'    => 'Asesoría personalizada',
                'paso3_desc'      => 'Sin compromiso. Solo soluciones reales para tu caso.',
            ]
        );
    }

    public static function labels(): array {
        return [
            'vendedor'          => 'Quiere vender',
            'comprador'         => 'Quiere comprar',
            'arrendatario'      => 'Busca renta',
            'propietario_renta' => 'Quiere rentar su propiedad',
            'b2b'               => 'Desarrollador / Inversionista',
            'contacto'          => 'Contacto general',
        ];
    }
}
