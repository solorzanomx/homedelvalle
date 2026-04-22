<?php

namespace App\Events;

use App\Models\GoogleSignatureRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentoFirmadoGoogle
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GoogleSignatureRequest $signatureRequest,
    ) {}
}
