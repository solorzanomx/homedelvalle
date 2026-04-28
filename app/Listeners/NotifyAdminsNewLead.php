<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Models\Notification;
use App\Models\User;

class NotifyAdminsNewLead
{
    public function handle(FormSubmitted $event): void
    {
        $sub = $event->submission;

        $typeLabels = [
            'vendedor'  => 'Vendedor',
            'comprador' => 'Comprador',
            'b2b'       => 'B2B',
            'contacto'  => 'Contacto',
        ];

        $title = 'Nuevo lead · ' . $sub->full_name;
        $body  = ($typeLabels[$sub->form_type] ?? ucfirst($sub->form_type))
               . ' · ' . $sub->email;
        $url   = '/admin/form-submissions/' . $sub->id;

        // Notificar a todos los usuarios admin/super_admin
        User::whereIn('role', ['admin', 'super_admin'])
            ->get()
            ->each(function (User $admin) use ($sub, $title, $body, $url) {
                Notification::create([
                    'user_id'      => $admin->id,
                    'from_user_id' => null,
                    'type'         => 'new_lead',
                    'title'        => $title,
                    'body'         => $body,
                    'data'         => [
                        'url'          => $url,
                        'submission_id'=> $sub->id,
                        'form_type'    => $sub->form_type,
                        'lead_tag'     => $sub->lead_tag,
                    ],
                    'read_at' => null,
                ]);
            });
    }
}
