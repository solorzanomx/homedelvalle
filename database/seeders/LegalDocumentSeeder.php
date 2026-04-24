<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $existing = LegalDocument::where('slug', 'contrato-confidencialidad')->first();
        if ($existing) {
            return; // Ya existe, no duplicar
        }

        $document = LegalDocument::create([
            'title'            => 'Acuerdo de Confidencialidad y Protección de Datos',
            'slug'             => 'contrato-confidencialidad',
            'type'             => 'contrato',
            'is_public'        => false,
            'status'           => 'published',
            'meta_description' => 'Contrato de confidencialidad para propietarios que comparten documentación de su inmueble con Home del Valle.',
            'created_by'       => null,
        ]);

        $document->createNewVersion($this->templateHtml(), 'Versión inicial', null);
    }

    private function templateHtml(): string
    {
        return <<<'HTML'
<div style="font-family: Georgia, serif; max-width: 800px; margin: 0 auto; padding: 40px; color: #1a1a1a; line-height: 1.7;">

  <div style="text-align: center; margin-bottom: 32px; border-bottom: 2px solid #1a1a1a; padding-bottom: 20px;">
    <p style="font-size: 13px; margin: 0; color: #555;">Home del Valle Bienes Raíces</p>
    <p style="font-size: 13px; margin: 0; color: #555;">Alcaldía Benito Juárez · Ciudad de México</p>
    <p style="font-size: 13px; margin: 0; color: #555;">contacto@homedelvalle.mx · homedelvalle.mx</p>
    <p style="font-size: 11px; margin: 8px 0 0; letter-spacing: 2px; text-transform: uppercase; color: #888;">Documento Legal</p>
    <h1 style="font-size: 22px; margin: 8px 0 0;">Acuerdo de Confidencialidad<br>y Protección de Datos</h1>
    <p style="margin: 12px 0 0;">Ciudad de México, a {{fecha}}</p>
  </div>

  <p style="font-style: italic; background: #f9f9f9; padding: 12px 16px; border-left: 3px solid #ccc; font-size: 14px; margin-bottom: 28px;">
    Este acuerdo garantiza que toda información y documentación que usted comparta con Home del Valle Bienes Raíces será tratada con absoluta confidencialidad, usada únicamente para los fines que usted autorice, y protegida contra cualquier divulgación no autorizada.
  </p>

  <h2 style="font-size: 15px; margin-top: 28px;">I. Partes del Acuerdo</h2>
  <p>El presente Acuerdo de Confidencialidad y Protección de Datos (en adelante, "el Acuerdo") se celebra entre:</p>

  <h3 style="font-size: 14px; margin-top: 16px;">A) El Propietario</h3>
  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
    <tr>
      <td style="padding: 6px 0; width: 50%;">Nombre completo: <strong>{{nombre}}</strong></td>
      <td style="padding: 6px 0;"></td>
    </tr>
    <tr>
      <td style="padding: 6px 0;">CURP: <strong>{{curp}}</strong></td>
      <td style="padding: 6px 0;">RFC: <strong>{{rfc}}</strong></td>
    </tr>
    <tr>
      <td colspan="2" style="padding: 6px 0;">Domicilio: <strong>{{domicilio}}</strong></td>
    </tr>
    <tr>
      <td style="padding: 6px 0;">Teléfono: <strong>{{telefono}}</strong></td>
      <td style="padding: 6px 0;">Correo: <strong>{{correo}}</strong></td>
    </tr>
  </table>

  <h3 style="font-size: 14px; margin-top: 16px;">B) Home del Valle Bienes Raíces</h3>
  <p>Representada por <strong>Ana Laura Monsivais</strong>, Directora General, con domicilio en Alcaldía Benito Juárez, Ciudad de México; correo contacto@homedelvalle.mx.</p>

  <h2 style="font-size: 15px; margin-top: 28px;">II. Objeto del Acuerdo</h2>
  <p>El Propietario comparte con Home del Valle Bienes Raíces documentación relacionada con su inmueble ubicado en:</p>
  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
    <tr>
      <td style="padding: 6px 0;" colspan="2">Dirección del inmueble: <strong>{{direccion_inmueble}}</strong></td>
    </tr>
    <tr>
      <td style="padding: 6px 0;">Colonia: <strong>{{colonia}}</strong></td>
      <td style="padding: 6px 0;">Alcaldía: Benito Juárez, CDMX</td>
    </tr>
  </table>
  <p>Home del Valle Bienes Raíces se compromete a recibir, resguardar y utilizar dicha documentación exclusivamente para analizar el potencial de venta o desarrollo del inmueble antes señalado, y para gestionar, en su caso, la operación de compraventa acordada con el Propietario.</p>

  <h2 style="font-size: 15px; margin-top: 28px;">III. Documentación Cubierta</h2>
  <p>El presente Acuerdo protege toda la documentación que el Propietario entregue a Home del Valle Bienes Raíces, incluyendo pero no limitándose a:</p>
  <p><strong>Documentos de identidad y fiscales</strong></p>
  <ul>
    <li>Credencial para Votar (INE/IFE)</li>
    <li>Clave Única de Registro de Población (CURP)</li>
    <li>Registro Federal de Contribuyentes (RFC) y Constancia de Situación Fiscal</li>
    <li>Pasaporte u otro documento de identidad oficial</li>
  </ul>
  <p><strong>Documentos de propiedad y legales</strong></p>
  <ul>
    <li>Escritura pública o título de propiedad</li>
    <li>Boleta predial y recibos de pago de impuesto predial</li>
    <li>Recibo de agua y demás servicios</li>
    <li>Planos arquitectónicos, memorias descriptivas o licencias de construcción</li>
    <li>Poderes notariales, testamentos o acuerdos de sucesión relacionados con el inmueble</li>
    <li>Cualquier otro documento que el Propietario considere necesario compartir para la gestión del inmueble</li>
  </ul>

  <h2 style="font-size: 15px; margin-top: 28px;">IV. Obligaciones de Home del Valle Bienes Raíces</h2>
  <p>Home del Valle Bienes Raíces se obliga a:</p>
  <ol>
    <li>Recibir y custodiar los documentos con el mayor nivel de cuidado y discreción posible.</li>
    <li>Utilizar la información y documentos únicamente para los fines autorizados expresamente por el Propietario en este Acuerdo.</li>
    <li>No revelar, divulgar, publicar, vender, arrendar, ni transferir a terceros ningún documento ni información obtenida del Propietario, sin su consentimiento previo y por escrito.</li>
    <li>Restringir el acceso a la documentación exclusivamente al personal directamente involucrado en la operación inmobiliaria objeto de este Acuerdo.</li>
    <li>Implementar medidas razonables de seguridad física y digital para proteger los documentos recibidos.</li>
    <li>Devolver o destruir los documentos originales o copias, a petición del Propietario, una vez concluida la relación de servicio.</li>
  </ol>

  <h2 style="font-size: 15px; margin-top: 28px;">V. Excepciones a la Confidencialidad</h2>
  <p>Las obligaciones de confidencialidad no aplicarán cuando:</p>
  <ol>
    <li>La información sea requerida por una autoridad judicial o administrativa competente mediante mandamiento legal escrito y debidamente notificado al Propietario.</li>
    <li>El Propietario otorgue autorización expresa y por escrito para compartir información con un tercero específico (por ejemplo, un notario público, institución bancaria o autoridad catastral).</li>
    <li>La información sea de dominio público por causas no imputables a Home del Valle Bienes Raíces.</li>
  </ol>

  <h2 style="font-size: 15px; margin-top: 28px;">VI. Derechos del Propietario</h2>
  <p>En todo momento, el Propietario tiene derecho a:</p>
  <ol>
    <li>Conocer qué documentos ha compartido con Home del Valle Bienes Raíces y para qué fin están siendo utilizados.</li>
    <li>Solicitar en cualquier momento la devolución de sus documentos originales.</li>
    <li>Revocar la autorización de uso de su información, con efectos a partir de la fecha de notificación por escrito.</li>
    <li>Presentar una queja o denuncia ante el Instituto Nacional de Transparencia, Acceso a la Información y Protección de Datos Personales (INAI) si considera que sus datos han sido manejados de manera indebida.</li>
  </ol>

  <h2 style="font-size: 15px; margin-top: 28px;">VII. Vigencia</h2>
  <p>Este Acuerdo tiene vigencia a partir de la fecha de su firma y se mantendrá activo durante toda la relación de servicio entre las partes. Las obligaciones de confidencialidad sobre los documentos compartidos permanecerán vigentes de manera indefinida, incluso después de concluida dicha relación.</p>

  <h2 style="font-size: 15px; margin-top: 28px;">VIII. Legislación Aplicable</h2>
  <p>El presente Acuerdo se rige por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP), su Reglamento, los Lineamientos del Aviso de Privacidad, y demás disposiciones aplicables en los Estados Unidos Mexicanos. Cualquier controversia derivada de este Acuerdo será sometida a la jurisdicción de los tribunales competentes de la Ciudad de México.</p>

  <h2 style="font-size: 15px; margin-top: 28px;">IX. Aceptación</h2>
  <p>Habiendo leído y comprendido el contenido del presente Acuerdo, las partes lo suscriben de conformidad en la Ciudad de México, en la fecha indicada al inicio de este documento.</p>

  <table style="width: 100%; margin-top: 48px; font-size: 14px;">
    <tr>
      <td style="width: 50%; vertical-align: top; padding-right: 24px;">
        <p><strong>El Propietario</strong></p>
        <p>Nombre: {{nombre}}</p>
        <br><br>
        <p style="border-top: 1px solid #333; padding-top: 4px;">Firma autógrafa</p>
      </td>
      <td style="width: 50%; vertical-align: top; padding-left: 24px;">
        <p><strong>Home del Valle Bienes Raíces</strong></p>
        <p>Ana Laura Monsivais<br>Directora General</p>
        <br>
        <p>Fecha: ___________________________________</p>
      </td>
    </tr>
  </table>

  <p style="text-align: center; font-size: 12px; color: #888; margin-top: 48px; border-top: 1px solid #ddd; padding-top: 12px;">
    Home del Valle Bienes Raíces · Alcaldía Benito Juárez, CDMX · homedelvalle.mx<br>
    <em>"Pocos inmuebles. Más control. Mejores resultados."</em>
  </p>

</div>
HTML;
    }
}
