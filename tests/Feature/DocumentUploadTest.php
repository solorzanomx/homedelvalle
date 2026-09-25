<?php

namespace Tests\Feature;

use App\Services\DocumentQualityService;
use App\Support\DocumentUploadGuide;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Protege el comportamiento de la subida guiada de documentos
 * (docs/funcionalidades/documentos-y-revision.md). Sin base de datos ni IA.
 */
class DocumentUploadTest extends TestCase
{
    private function fakeImage(int $w, int $h): UploadedFile
    {
        $path = sys_get_temp_dir() . "/hdv-test-{$w}x{$h}.jpg";
        $im = imagecreatetruecolor($w, $h);
        for ($i = 0; $i < 300; $i++) {
            imagefilledrectangle($im, rand(0, $w), rand(0, $h), rand(0, $w), rand(0, $h), rand(0, 16777215));
        }
        imagejpeg($im, $path, 90);

        return new UploadedFile($path, basename($path), 'image/jpeg', null, true);
    }

    private function fakePdf(string $body): UploadedFile
    {
        $path = sys_get_temp_dir() . '/hdv-test-' . md5($body) . '.pdf';
        file_put_contents($path, $body);

        return new UploadedFile($path, basename($path), 'application/pdf', null, true);
    }

    public function test_guide_classifies_document_kinds(): void
    {
        $this->assertSame('statement', DocumentUploadGuide::kind('estado_cuenta'));
        $this->assertSame('utility', DocumentUploadGuide::kind('luz'));
        $this->assertSame('id', DocumentUploadGuide::kind('ine_frente'));
        $this->assertSame('payment', DocumentUploadGuide::kind('comprobante_apartado'));
        $this->assertSame('generic', DocumentUploadGuide::kind('categoria_inexistente'));
    }

    public function test_guide_always_tells_client_not_to_photograph_a_screen(): void
    {
        foreach (['estado_cuenta', 'luz', 'escritura', 'ine_frente', null] as $cat) {
            $dont = implode(' ', DocumentUploadGuide::for($cat)['dont']);
            $this->assertStringContainsStringIgnoringCase('pantalla', $dont, "Falta la advertencia de pantalla en {$cat}");
        }
    }

    public function test_every_categorised_key_exists_in_document_categories(): void
    {
        foreach (array_keys(DocumentUploadGuide::KINDS) as $key) {
            $this->assertArrayHasKey($key, \App\Models\Document::CATEGORIES, "La guía menciona '{$key}' que no existe en Document::CATEGORIES");
        }
    }

    public function test_inbox_excludes_only_real_categories_and_route_exists(): void
    {
        foreach (\App\Support\DocumentReviewInbox::GENERATED as $key) {
            $this->assertArrayHasKey($key, \App\Models\Document::CATEGORIES, "GENERATED menciona '{$key}' que no existe");
        }
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('documents.inbox'));
        $this->assertTrue(class_exists(\App\Console\Commands\CheckDocumentsPendingReview::class));
    }

    public function test_tiny_image_is_blocked_and_cannot_be_bypassed(): void
    {
        $svc = new DocumentQualityService();
        for ($i = 0; $i < 4; $i++) {
            $r = $svc->gate($this->fakeImage(200, 200), 'estado_cuenta', 990001);
            $this->assertNotNull($r['block'], "Intento {$i}: una imagen diminuta nunca debe pasar");
        }
    }

    public function test_encrypted_and_invalid_pdfs_are_blocked(): void
    {
        $svc = new DocumentQualityService();

        $enc = $svc->gate($this->fakePdf("%PDF-1.4\n1 0 obj<</Encrypt 2 0 R>>endobj"), 'estado_cuenta', 990002);
        $this->assertStringContainsString('contraseña', (string) $enc['block']);

        $ok = $svc->gate($this->fakePdf("%PDF-1.4\n1 0 obj<<>>endobj\n%%EOF"), 'comprobante_apartado', 990002);
        $this->assertNull($ok['block'], 'Un PDF válido de comprobante de pago debe aceptarse');
    }
}
