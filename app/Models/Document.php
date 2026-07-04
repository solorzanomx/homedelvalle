<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['rental_process_id', 'operation_id', 'client_id', 'property_id', 'captacion_id', 'valuation_id', 'uploaded_by', 'category', 'label', 'file_path', 'file_name', 'mime_type', 'file_size', 'status', 'is_captacion_required', 'captacion_status', 'rejection_reason', 'verified_at', 'verified_by',];
    const CATEGORIES = [
        'commission_contract' => 'Contrato de Comision',
        'escritura' => 'Escritura',
        'predial' => 'Predial',
        'owner_id' => 'INE Propietario',
        'tenant_id' => 'INE Arrendatario',
        'identificacion' => 'Identificación Oficial',
        'curp' => 'CURP',
        'comprobante_domicilio' => 'Comprobante de Domicilio',
        'acta_matrimonio' => 'Acta de Matrimonio',
        'testamento' => 'Testamento',
        'declaratoria_herederos' => 'Declaratoria de Herederos',
        'planos' => 'Planos del Inmueble',
        'reglamento_condominio' => 'Reglamento de Condominio',
        'proof_of_income' => 'Comprobante de Ingresos',
        'credit_report' => 'Reporte Crediticio',
        'references' => 'Referencias',
        'rental_contract' => 'Contrato de Arrendamiento',
        'poliza_contract' => 'Poliza Juridica',
        'other' => 'Otro',
        'presentation_pdf'    => 'Presentación Inicial PDF',
        'opinion_valor'       => 'Opinión de Valor PDF',
        'propuesta_servicios' => 'Propuesta de Servicios PDF',
        'oferta_compra'       => 'Carta Oferta de Compra',
        'contrato_exclusiva'  => 'Contrato de Exclusiva',
        'contrato_compraventa' => 'Contrato de Compraventa',
        // Expediente del cliente
        'ine_frente'            => 'INE — Frente',
        'ine_reverso'           => 'INE — Reverso',
        'pasaporte'             => 'Pasaporte',
        'libertad_gravamen'     => 'Certificado de Libertad de Gravamen',
        'agua'                  => 'Boleta de Agua',
        'acta_nacimiento'       => 'Acta de Nacimiento',
        'estado_cuenta'         => 'Estado de Cuenta Bancario',
        'carta_preautorizacion' => 'Carta de Preautorización (Crédito)',
        'cfdi_honorarios'       => 'CFDI de Honorarios',
        'nomina'                => 'Recibo de Nómina',
        // Aval
        'aval_ine_frente'       => 'INE Aval — Frente',
        'aval_ine_reverso'      => 'INE Aval — Reverso',
        'aval_escritura'        => 'Escritura del Inmueble (Aval)',
        'aval_predial'          => 'Predial del Inmueble (Aval)',
        'aval_libertad_gravamen'=> 'Libertad de Gravamen (Aval)',
        // Pagarés
        'pagare'                => 'Pagaré',
    ];

    const STATUSES = [
        'pending' => 'Pendiente',
        'received' => 'Recibido',
        'verified' => 'Verificado',
        'rejected' => 'Rechazado',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function captacion() { return $this->belongsTo(Captacion::class); }
    public function valuation() { return $this->belongsTo(PropertyValuation::class, 'valuation_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '';
        if ($this->file_size < 1024) return $this->file_size . ' B';
        if ($this->file_size < 1048576) return round($this->file_size / 1024, 1) . ' KB';
        return round($this->file_size / 1048576, 1) . ' MB';
    }
}
