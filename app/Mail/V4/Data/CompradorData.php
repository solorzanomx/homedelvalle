<?php

namespace App\Mail\V4\Data;

readonly class CompradorData
{
    public function __construct(
        public string $email,
        public string $colonia,
        public string $titulo,
        public string $metros,
        public string $recamaras,
        public string $banos,
        public string $estacionamientos,
        public string $precio,
        public ?string $foto_url = null,
    ) {}
}
