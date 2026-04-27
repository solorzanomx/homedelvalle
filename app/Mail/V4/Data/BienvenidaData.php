<?php

namespace App\Mail\V4\Data;

readonly class BienvenidaData
{
    public function __construct(
        public string $email,
        public string $usuario,
        public string $password_temporal,
        public string $url_acceso,
    ) {}
}
