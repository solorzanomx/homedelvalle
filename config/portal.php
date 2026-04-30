<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portal del Cliente — Configuración
    | miportal.homedelvalle.mx
    |--------------------------------------------------------------------------
    */

    // Activar en Portal-5 cuando se hayan probado end-to-end los listeners.
    // Mientras false: los listeners están definidos pero no ejecutan.
    'auto_create_accounts' => env('PORTAL_AUTO_CREATE_ACCOUNTS', false),

    // Dominio del portal (sin https://)
    'domain' => env('PORTAL_DOMAIN', 'miportal.homedelvalle.mx'),

    // URL base del portal
    'url' => env('PORTAL_URL', 'https://miportal.homedelvalle.mx'),

    // TTL del token de invitación en días
    'invitation_ttl_days' => 7,

    // Activar banner de impersonación
    'impersonation_banner' => true,
];
