<?php

namespace Tests\Feature;

use App\Support\SecureFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Protege el almacenamiento privado de archivos sensibles (docs/funcionalidades/seguridad-archivos.md). */
class SecureFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_new_uploads_go_to_the_private_disk_never_the_public_one(): void
    {
        $path = SecureFiles::store(UploadedFile::fake()->image('ine.jpg'), 'documents/client-1');

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->assertNotNull(SecureFiles::locate($path));
    }

    public function test_legacy_public_files_still_resolve_until_migrated_and_delete_removes_both(): void
    {
        Storage::disk('public')->put('documents/client-9/old.pdf', 'legacy');

        $this->assertTrue(SecureFiles::isLegacyPublic('documents/client-9/old.pdf'));
        $this->assertSame('legacy', SecureFiles::get('documents/client-9/old.pdf'));

        SecureFiles::put('documents/client-9/old.pdf', 'private-copy');
        $this->assertFalse(SecureFiles::isLegacyPublic('documents/client-9/old.pdf'), 'una vez en el privado ya no es legacy');
        $this->assertSame('private-copy', SecureFiles::get('documents/client-9/old.pdf'), 'el privado gana sobre el público');

        SecureFiles::delete('documents/client-9/old.pdf');
        Storage::disk('local')->assertMissing('documents/client-9/old.pdf');
        Storage::disk('public')->assertMissing('documents/client-9/old.pdf');
    }

    public function test_responses_carry_safe_headers_and_missing_files_return_null(): void
    {
        SecureFiles::put('contracts/c.pdf', '%PDF-1.4');
        $r = SecureFiles::response('contracts/c.pdf', 'Contrato.pdf', 'application/pdf');

        $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
        $this->assertSame('nosniff', $r->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('attachment', $r->headers->get('Content-Disposition'));
        $this->assertNull(SecureFiles::response('contracts/no-existe.pdf'));
        $this->assertNull(SecureFiles::locate(null));
    }

    public function test_client_users_cannot_reach_staff_document_routes(): void
    {
        foreach (['documents.download', 'documents.preview', 'documents.update-status', 'documents.destroy', 'documents.inbox', 'documents.bulk-approve'] as $name) {
            $mw = \Illuminate\Support\Facades\Route::getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('viewer', $mw, "{$name} debe exigir rol de personal (viewer)");
        }
    }
}
