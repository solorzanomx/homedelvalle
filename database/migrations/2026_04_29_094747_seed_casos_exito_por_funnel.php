<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $casos = [
            // ── Funnel 1: Venta ──────────────────────────────────────────────
            [
                'name'           => 'Claudia Reyes',
                'role'           => 'Vendedora · Del Valle Centro',
                'location'       => 'Del Valle Centro, BJ',
                'content'        => 'Tenía el departamento parado desde hacía 5 meses con otra inmobiliaria. Con Home del Valle lo valuaron en una semana, ajustamos el precio con datos reales del mercado y cerramos en 38 días. El acompañamiento jurídico fue impecable, sin sorpresas al momento de firmar escrituras.',
                'operation_type' => 'Venta',
                'ticket'         => '$4,200,000',
                'time_in_market' => '38 días',
                'rating'         => 5,
                'is_featured'    => true,
                'is_active'      => true,
                'type'           => 'text',
                'sort_order'     => 10,
            ],
            // ── Funnel 2: Compra ─────────────────────────────────────────────
            [
                'name'           => 'Rodrigo Menéndez',
                'role'           => 'Comprador · Narvarte Poniente',
                'location'       => 'Narvarte Poniente, BJ',
                'content'        => 'Le di mi brief: 2 recámaras, luz natural, planta baja o elevador, máximo $3.5 MDP en Narvarte o Del Valle. En tres días me enviaron cuatro opciones que cumplían al 100%. Visitamos dos, me quedé con la segunda. No perdí tiempo viendo cosas que no eran. El due diligence legal me dio total tranquilidad antes de firmar.',
                'operation_type' => 'Compra',
                'ticket'         => '$3,350,000',
                'time_in_market' => '3 semanas desde brief hasta escritura',
                'rating'         => 5,
                'is_featured'    => true,
                'is_active'      => true,
                'type'           => 'text',
                'sort_order'     => 20,
            ],
            // ── Funnel 3: Renta para vivir (arrendatario) ────────────────────
            [
                'name'           => 'Sofía Blanco',
                'role'           => 'Arrendataria · Del Valle Norte',
                'location'       => 'Del Valle Norte, BJ',
                'content'        => 'Llevaba semanas perdida en portales con opciones que no coincidían con lo que buscaba o ya estaban rentadas. Les mandé mi brief: pet-friendly, 2 recámaras, menos de $20,000 en Del Valle Norte o Narvarte. En 48 horas me llegaron 4 opciones curadas. Fui a ver 2, elegí una, y en 10 días ya estaba firmando. Sin agentes que insisten, sin catálogos masivos.',
                'operation_type' => 'Renta',
                'ticket'         => '$18,500 / mes',
                'time_in_market' => '48 horas · 2 visitas · 10 días al contrato',
                'rating'         => 5,
                'is_featured'    => true,
                'is_active'      => true,
                'type'           => 'text',
                'sort_order'     => 30,
            ],
            // ── Funnel 4: Propietario en renta ───────────────────────────────
            [
                'name'           => 'Mauricio Garza',
                'role'           => 'Propietario · Nápoles',
                'location'       => 'Nápoles, BJ',
                'content'        => 'Tenía el departamento vacío dos meses. Me propusieron un precio realista (no inflado para captar la firma), tomaron fotos profesionales y en 25 días tenía inquilino calificado con póliza jurídica activa. Opté por administración integral y ahora no me preocupo por cobranza ni mantenimiento. La primer renta llegó a tiempo, sin excusas.',
                'operation_type' => 'Renta',
                'ticket'         => '$21,000 / mes',
                'time_in_market' => '25 días para colocar',
                'rating'         => 5,
                'is_featured'    => true,
                'is_active'      => true,
                'type'           => 'text',
                'sort_order'     => 40,
            ],
        ];

        foreach ($casos as $caso) {
            // Skip if a testimonial with same name+operation_type already exists
            $exists = DB::table('testimonials')
                ->where('name', $caso['name'])
                ->where('operation_type', $caso['operation_type'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('testimonials')->insert(array_merge($caso, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('testimonials')
            ->whereIn('name', ['Claudia Reyes', 'Rodrigo Menéndez', 'Sofía Blanco', 'Mauricio Garza'])
            ->whereIn('operation_type', ['Venta', 'Compra', 'Renta'])
            ->delete();
    }
};
