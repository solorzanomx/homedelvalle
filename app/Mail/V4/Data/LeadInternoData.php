<?php

namespace App\Mail\V4\Data;

readonly class LeadInternoData
{
    public function __construct(
        public string $nombre,
        public string $email,
        public string $telefono,
        public string $origen,
        public string $fecha,
        public string $mensaje,
    ) {}
}
