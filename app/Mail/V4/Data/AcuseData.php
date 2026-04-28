<?php

namespace App\Mail\V4\Data;

readonly class AcuseData
{
    public function __construct(
        public string $folio,
        public string $email,
        public string $form_type = 'contacto',
        public string $nombre    = '',
    ) {}
}
