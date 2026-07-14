<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Las cláusulas del adéndum ganaron la convención tipográfica correcta
 * (monto en negrita, letra en paréntesis sin negrita). DocumentClause::text
 * prioriza el valor guardado en BD sobre el default del código (gotcha ya
 * documentado) — si alguien abrió el editor y guardó sin cambios, quedaron
 * los textos viejos persistidos. Se eliminan SOLO las filas cuyo valor es
 * idéntico al default anterior (una edición manual real no se toca).
 */
return new class extends Migration
{
    private const OLD_DEFAULTS = [
        'oferta_economica' => '<strong>SEGUNDA. Oferta económica.</strong> Las partes reconocen que el comprador {{comprador}} formuló una oferta de compra por la cantidad de <strong>{{precio}} ({{precio_letras}})</strong>, misma que será cubierta de la siguiente forma:',
        'comision_unica' => '<strong>TERCERA. Reconocimiento del derecho de comisión.</strong> EL PROPIETARIO reconoce expresamente que el comprador antes señalado fue obtenido exclusivamente por las gestiones profesionales de HOME DEL VALLE BIENES RAÍCES, por lo que reconoce el derecho de ésta al cobro de la comisión mercantil correspondiente. En consecuencia, EL PROPIETARIO se obliga a pagar a HOME DEL VALLE BIENES RAÍCES la cantidad de <strong>{{comision}} ({{comision_letras}})</strong> en una sola exhibición, al momento de la firma de la escritura definitiva de compraventa.',
        'comision_proporcional' => '<strong>TERCERA. Reconocimiento del derecho de comisión.</strong> EL PROPIETARIO reconoce expresamente que el comprador antes señalado fue obtenido exclusivamente por las gestiones profesionales de HOME DEL VALLE BIENES RAÍCES, por lo que reconoce el derecho de ésta al cobro de la comisión mercantil correspondiente por la cantidad total de <strong>{{comision}} ({{comision_letras}})</strong>, que EL PROPIETARIO se obliga a pagar en proporción a los pagos recibidos del comprador, de la siguiente forma: <strong>a)</strong> {{comision_contrato}} ({{comision_contrato_letras}}) al momento de la firma del Contrato de Promesa de Compraventa; <strong>b)</strong> {{comision_escritura}} ({{comision_escritura_letras}}) al momento de la firma de la escritura definitiva de compraventa.',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('document_clauses')) {
            return;
        }

        foreach (self::OLD_DEFAULTS as $key => $oldValue) {
            DB::table('document_clauses')
                ->where('document_key', 'adendum_comision')
                ->where('clause_key', $key)
                ->where('value', $oldValue)
                ->delete();
        }
    }

    public function down(): void
    {
        // Limpieza de defaults obsoletos — sin reversa.
    }
};
