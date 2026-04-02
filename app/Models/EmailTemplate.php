<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'subject', 'body', 'body_text'])]
class EmailTemplate extends Model
{
    /**
     * Replace dynamic variables in subject, body and body_text.
     * Supported: {{Nombre}}, {{Apellido}}, {{Email}}, {{Password}}, {{Fecha}}, {{Rol}}, {{Sitio}}
     */
    public function render(array $variables): array
    {
        $subject = $this->subject;
        $body = $this->body;
        $bodyText = $this->body_text ?? strip_tags($this->body);

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value ?? '', $subject);
            $body = str_replace($placeholder, $value ?? '', $body);
            $bodyText = str_replace($placeholder, $value ?? '', $bodyText);
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'body_text' => $bodyText,
        ];
    }
}
