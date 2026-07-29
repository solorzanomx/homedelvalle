<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Endpoint público del pixel de apertura de correo (1x1 GIF transparente).
 * Debe responder siempre la imagen, incluso con token inválido/expirado —
 * un pixel roto en el correo del destinatario se ve mal y no aporta nada.
 */
class EmailOpenController extends Controller
{
    private const PIXEL_GIF = "GIF89a\x01\x00\x01\x00\x80\x00\x00\xFF\xFF\xFF\x00\x00\x00!\xF9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00;";

    public function pixel(Request $request, string $token): Response
    {
        $message = Message::where('external_id', $token)->first();

        if ($message) {
            $isFirstOpen = is_null($message->opened_at);
            $message->markOpened();

            if ($isFirstOpen && $message->trackable_type === Broker::class && $message->trackable_id) {
                Broker::whereKey($message->trackable_id)->update(['email_opened_at' => now()]);
            }
        }

        return response(self::PIXEL_GIF, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => (string) strlen(self::PIXEL_GIF),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
