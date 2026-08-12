<?php

use App\Models\ContractClause;
use App\Models\ContractTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $template = ContractTemplate::firstOrCreate(
            ['name' => 'Promesa de Compraventa — Estándar', 'type' => 'sale'],
            ['uses_clauses' => true, 'is_active' => true, 'body' => '', 'variables' => []]
        );

        if (!$template->uses_clauses) {
            $template->update(['uses_clauses' => true]);
        }

        if ($template->clauses()->exists()) {
            return; // idempotente — no duplicar si ya se sembró
        }

        $order = 0;
        foreach ($this->clauses() as $clause) {
            ContractClause::create([
                'clauseable_type' => ContractTemplate::class,
                'clauseable_id' => $template->id,
                'key' => $clause['key'],
                'title' => $clause['title'],
                'body' => $clause['body'],
                'section' => $clause['section'],
                'sort_order' => $order++,
                'is_locked' => $clause['section'] === 'firma',
            ]);
        }
    }

    public function down(): void
    {
        $template = ContractTemplate::where('name', 'Promesa de Compraventa — Estándar')->where('type', 'sale')->first();
        if ($template) {
            $template->clauses()->delete();
            $template->delete();
        }
    }

    protected function clauses(): array
    {
        return [
            [
                'key' => 'declaracion_vendedor', 'section' => 'declaracion',
                'title' => 'I. – Declara "EL PROMITENTE VENDEDOR"',
                'body' => '<p>Declara "EL PROMITENTE VENDEDOR", bajo protesta de decir verdad, que es persona física, de nacionalidad mexicana, en pleno uso de sus facultades legales, con capacidad de ejercicio y legitimación suficiente para celebrar el presente contrato, identificándose de la siguiente manera: {{vendedor_nombre}}, quien se identifica con [TIPO Y NÚMERO DE IDENTIFICACIÓN OFICIAL], misma que exhibe en original para su cotejo, devolviéndosele una vez verificada su autenticidad.</p>
<div class="lettered-list">
  <div class="litem"><span class="ltext">Que adquirió la propiedad del inmueble objeto del presente contrato mediante [DATOS DE LA ESCRITURA PÚBLICA DE ADQUISICIÓN: número, fecha, notario, inscripción registral].</span></div>
  <div class="litem"><span class="ltext">El inmueble corresponde a {{propiedad_direccion}}, con las medidas, colindancias y superficie consignadas en la escritura de propiedad.</span></div>
  <div class="litem"><span class="ltext">Declara que el inmueble es de su exclusiva propiedad, libre de gravámenes, limitaciones de dominio, embargos o litigios.</span></div>
  <div class="litem"><span class="ltext">La entrega se efectuará libre de todo gravamen, limitación de dominio, embargo, litigio, afectación o adeudo de cualquier naturaleza, incluyendo contribuciones fiscales, cuotas de mantenimiento y servicios, obligándose "EL PROMITENTE VENDEDOR" a responder por el saneamiento para el caso de evicción en términos de ley, y a entregar el inmueble completamente desocupado, libre de personas y bienes y limpio en su totalidad.</span></div>
  <div class="litem"><span class="ltext">Que es su voluntad prometer vender el inmueble en los términos del presente contrato.</span></div>
</div>',
            ],
            [
                'key' => 'declaracion_comprador', 'section' => 'declaracion',
                'title' => 'II. – Declara "LA/EL PROMITENTE COMPRADOR(A)"',
                'body' => '<p>Declara "LA/EL PROMITENTE COMPRADOR(A)", bajo protesta de decir verdad:</p>
<div class="lettered-list">
  <div class="litem"><span class="ltext">{{comprador_nombre}}, que es persona física, de nacionalidad mexicana, con plena capacidad legal para contratar y obligarse, identificándose con [TIPO Y NÚMERO DE IDENTIFICACIÓN OFICIAL].</span></div>
  <div class="litem"><span class="ltext">Que cuenta con capacidad económica suficiente para adquirir el inmueble y que los recursos con los que cubrirá el precio son de procedencia lícita.</span></div>
  <div class="litem"><span class="ltext">Que conoce plenamente el estado físico, jurídico y material del inmueble y manifiesta su conformidad para adquirirlo en el estado en que actualmente se encuentra.</span></div>
  <div class="litem"><span class="ltext">Que es su voluntad adquirir el inmueble descrito, obligándose a comparecer al otorgamiento de la escritura pública definitiva.</span></div>
  <div class="litem"><span class="ltext">Que adquiere el inmueble ad corpus, por lo que cualquier diferencia de superficie no dará lugar a reclamación.</span></div>
</div>',
            ],
            [
                'key' => 'declaracion_conformidad', 'section' => 'declaracion',
                'title' => 'III. – Conformidad de "LAS PARTES"',
                'body' => '<p>"LAS PARTES" confirman que con las declaraciones anteriores, es su voluntad celebrar el presente contrato de promesa de compraventa, al tenor de las siguientes:</p>',
            ],
            [
                'key' => 'objeto', 'section' => 'clausula', 'title' => 'Primera. – Objeto',
                'body' => '<p>"EL PROMITENTE VENDEDOR" se obliga a VENDER, libre de todo gravamen, limitación de dominio, embargo, litigio o cualquier otra afectación que impida su libre transmisión, a favor de "LA/EL PROMITENTE COMPRADOR(A)", quien se obliga a COMPRAR, bajo la modalidad AD CORPUS, el inmueble descrito en la Declaración I del presente contrato, incluyendo todos sus usos, servidumbres, accesiones, instalaciones fijas y derechos inherentes. En consecuencia, "LAS PARTES" se obligan a celebrar el contrato definitivo de compraventa en escritura pública ante la fe de [NOTARIO Y NÚMERO DE NOTARÍA A CONFIRMAR], o quien legalmente lo sustituya, a más tardar el día {{fecha_limite_escrituracion}}, o antes si así lo acuerdan por escrito.</p>',
            ],
            [
                'key' => 'precio', 'section' => 'clausula', 'title' => 'Segunda. – Precio',
                'body' => '<p>El precio cierto y convenido por la compraventa prometida es la cantidad de {{precio_texto}}, que "LA/EL PROMITENTE COMPRADOR(A)" se obliga a pagar a "EL PROMITENTE VENDEDOR" en la forma y términos siguientes:</p>
<div class="lettered-list">
  <div class="litem"><span class="ltext">[DESCRIBIR ANTICIPO/DEPÓSITO EN GARANTÍA: monto, fecha, medio de pago].</span></div>
  <div class="litem"><span class="ltext">[DESCRIBIR SALDO DEL PRECIO: monto, forma y calendario de pago — transferencia, crédito hipotecario, etc.].</span></div>
  <div class="litem"><span class="ltext">[DESCRIBIR COMISIÓN POR INTERMEDIACIÓN, SI APLICA, Y A CARGO DE QUIÉN].</span></div>
</div>',
            ],
            [
                'key' => 'propiedad_posesion', 'section' => 'clausula', 'title' => 'Tercera. – Propiedad y Posesión',
                'body' => '<p>"EL PROMITENTE VENDEDOR" se obliga a otorgar y firmar la escritura pública definitiva de compraventa a favor de "LA/EL PROMITENTE COMPRADOR(A)" o de la persona física o moral que ésta designe por escrito con anterioridad a la firma de la escritura. La transmisión de la propiedad se efectuará libre de todo gravamen, limitación de dominio o adeudo, y la posesión jurídica y material del inmueble se entregará en el mismo acto de firma de la escritura pública definitiva, una vez cubierto en su totalidad el precio pactado.</p>',
            ],
            [
                'key' => 'escrituracion', 'section' => 'clausula', 'title' => 'Cuarta. – Escrituración',
                'body' => '<p>"LAS PARTES" se obligan a celebrar el contrato de compraventa prometido en escritura pública ante [NOTARIO Y NÚMERO DE NOTARÍA A CONFIRMAR], quedando obligadas a proporcionar toda la documentación, información y anticipos que les sea solicitada, en un plazo no mayor de tres (3) días hábiles contados a partir de la solicitud.</p>',
            ],
            [
                'key' => 'pacto_comisorio', 'section' => 'clausula', 'title' => 'Quinta. – Pacto Comisorio Expreso',
                'body' => '<p>Será causa de rescisión del presente contrato cualquier incumplimiento al contenido obligacional del mismo.</p>',
            ],
            [
                'key' => 'incumplimiento', 'section' => 'clausula', 'title' => 'Sexta. – Incumplimiento',
                'body' => '<p>En el supuesto de que, por causas injustificadas imputables a cualquiera de "LAS PARTES", no se celebre el contrato de compraventa prometido en escritura pública a más tardar el día {{fecha_limite_escrituracion}}, se configurará incumplimiento, el cual dará derecho a la parte cumplida a exigir de la parte incumplida el pago de una pena convencional equivalente a [MONTO DE LA PENA CONVENCIONAL], misma que deberá cubrirse en un plazo máximo de diez (10) días hábiles contados a partir del incumplimiento, conforme a las siguientes reglas:</p>
<div class="lettered-list">
  <div class="litem"><span class="ltext">Si el incumplimiento es imputable a "LA/EL PROMITENTE COMPRADOR(A)", ésta/éste deberá pagar la pena convencional pactada, cantidad que podrá cubrirse con cargo al depósito en garantía y/o a cualquier pago parcial del precio entregado.</span></div>
  <div class="litem"><span class="ltext">Si el incumplimiento es imputable a "EL PROMITENTE VENDEDOR", éste quedará obligado a restituir la totalidad de las cantidades recibidas, así como a cubrir la pena convencional pactada.</span></div>
  <div class="litem"><span class="ltext">Si alguna de "LAS PARTES", sin causa justificada, no acudiere a la firma de la escritura pública en la fecha convenida, contará con un plazo de diez (10) días naturales para subsanar el incumplimiento, transcurrido el cual se tendrá por rescindido de pleno derecho el presente contrato.</span></div>
  <div class="litem"><span class="ltext">"LAS PARTES" podrán, de común acuerdo, prorrogar la fecha de firma de la escritura definitiva por el tiempo razonablemente necesario, en caso de demoras administrativas, notariales o registrales atribuibles a terceros.</span></div>
  <div class="litem"><span class="ltext">No se considerará incumplimiento imputable a "LAS PARTES" el retraso ocasionado por causas atribuibles al Notario Público, Registro Público de la Propiedad, dependencias gubernamentales, instituciones bancarias o cualquier tercero.</span></div>
</div>',
            ],
            [
                'key' => 'erogaciones', 'section' => 'clausula', 'title' => 'Séptima. – Erogaciones',
                'body' => '<p>Todos los gastos, impuestos, derechos y honorarios que se causen con motivo de la celebración del contrato de compraventa prometido en escritura pública serán cubiertos por [PARTE RESPONSABLE A CONFIRMAR], excepto el Impuesto Sobre la Renta por enajenación, el cual será a cargo de "EL PROMITENTE VENDEDOR".</p>',
            ],
            [
                'key' => 'reserva_dominio', 'section' => 'clausula', 'title' => 'Octava. – Reserva de Dominio, Transmisión y Entrega',
                'body' => '<p>"EL PROMITENTE VENDEDOR" se reserva expresamente la propiedad del inmueble objeto del presente contrato, en términos de lo dispuesto por la legislación civil aplicable, hasta en tanto se cubra en su totalidad el precio pactado y se otorgue la escritura pública definitiva de compraventa ante notario público. La transmisión del dominio queda sujeta a la condición suspensiva consistente en el pago total del precio convenido y el otorgamiento de la escritura pública correspondiente, momento en el cual se realizará de manera simultánea la entrega de la posesión jurídica y material del inmueble, libre de todo gravamen, afectación o adeudo, y completamente desocupado.</p>',
            ],
            [
                'key' => 'saneamiento', 'section' => 'clausula', 'title' => 'Novena. – Saneamiento por Evicción',
                'body' => '<p>"EL PROMITENTE VENDEDOR" se obliga a responder del saneamiento para el caso de evicción, en los términos de ley, y se obliga, en dicho caso, a dejar en paz y salvo los derechos que por este contrato adquiere "LA/EL PROMITENTE COMPRADOR(A)".</p>',
            ],
            [
                'key' => 'modificaciones', 'section' => 'clausula', 'title' => 'Décima. – Modificaciones al Contrato',
                'body' => '<p>Cualquier convenio modificatorio al presente contrato deberá constar por escrito.</p>',
            ],
            [
                'key' => 'titulos_clausulas', 'section' => 'clausula', 'title' => 'Décima Primera. – Títulos de las Cláusulas',
                'body' => '<p>Los títulos de las cláusulas se han puesto con el exclusivo propósito de facilitar su lectura, por tanto, no necesariamente definen ni limitan el contenido de las mismas. Para efectos de interpretación de cada cláusula deberá atenderse exclusivamente a su contenido y de ninguna manera a su título.</p>',
            ],
            [
                'key' => 'legislacion_aplicable', 'section' => 'clausula', 'title' => 'Décima Segunda. – Legislación Aplicable',
                'body' => '<p>Para la interpretación, cumplimiento y ejecución del presente contrato, así como para todo lo no previsto expresamente en el mismo, "LAS PARTES" se someten a la legislación civil aplicable en la Ciudad de México. El presente contrato podrá ser suscrito mediante firma electrónica a través de plataformas digitales, reconociendo que dicha modalidad produce los mismos efectos legales que la firma autógrafa, de conformidad con el Código de Comercio y la legislación mexicana aplicable en materia de mensajes de datos y medios electrónicos.</p>',
            ],
            [
                'key' => 'notificaciones', 'section' => 'clausula', 'title' => 'Décima Tercera. – Notificaciones',
                'body' => '<p>"LAS PARTES" señalan como domicilios convencionales para oír y recibir todo tipo de notificaciones, requerimientos y comunicaciones relacionadas con el presente contrato, los siguientes:</p>
<table class="notif-table">
  <tr><td>"EL PROMITENTE VENDEDOR"</td><td>[DOMICILIO, EMAIL Y TELÉFONO A CONFIRMAR]</td></tr>
  <tr><td>"LA/EL PROMITENTE COMPRADOR(A)"</td><td>[DOMICILIO, EMAIL Y TELÉFONO A CONFIRMAR]</td></tr>
</table>
<p>Cualquier notificación derivada del presente contrato podrá realizarse válidamente en dichos domicilios o correos electrónicos, surtiendo plenos efectos legales desde su entrega, recepción o confirmación. Las partes se obligan a notificar por escrito cualquier cambio de domicilio con al menos cinco días hábiles de anticipación.</p>',
            ],
            [
                'key' => 'vigencia', 'section' => 'clausula', 'title' => 'Décima Cuarta. – Vigencia',
                'body' => '<p>El presente contrato tendrá vigencia a partir de la fecha de su firma y permanecerá en vigor hasta en tanto se cumplan total y cabalmente todas y cada una de las obligaciones asumidas por "LAS PARTES" en las cláusulas de este instrumento, incluyendo, en su caso, las que resulten posteriores a la firma de la escritura definitiva de compraventa.</p>',
            ],
            [
                'key' => 'ausencia_vicios', 'section' => 'clausula', 'title' => 'Décima Quinta. – Ausencia de Vicios de la Voluntad',
                'body' => '<p>"LAS PARTES" manifiestan, bajo protesta de decir verdad, que en la celebración del presente contrato no ha mediado error, dolo, mala fe, violencia, coacción ni ningún otro vicio de la voluntad que pudiera afectarlo, viciarlo o invalidarlo, celebrándolo de manera libre, consciente y con pleno consentimiento.</p>',
            ],
            [
                'key' => 'firma', 'section' => 'firma', 'title' => 'Décima Sexta. – Firma',
                'body' => '<p>Leído y revisado que fue el presente contrato, y enteradas "LAS PARTES" de su contenido, alcance legal y fuerza obligatoria, lo firman por triplicado en la Ciudad de México, a {{fecha_firma_texto}}, quedando un ejemplar en poder de cada una de ellas. Para mayor constancia y seguridad jurídica, "LAS PARTES" firman al margen de todas y cada una de las fojas que integran este contrato y, al calce de la última, donde se asientan sus nombres completos y firmas autógrafas.</p>',
            ],
        ];
    }
};
