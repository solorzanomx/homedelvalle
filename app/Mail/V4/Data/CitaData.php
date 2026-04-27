<?php

namespace App\Mail\V4\Data;

readonly class CitaData
{
    public function __construct(
        public string $email,
        public string $dia_semana,
        public string $dia,
        public string $mes,
        public string $anio,
        public string $hora,
        public string $duracion,
        public string $direccion,
        public string $colonia,
        public string $asesor,
    ) {}
}
