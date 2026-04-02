<?php

namespace App\Helpers;

class MentionHelper
{
    /**
     * Render @mentions in text as highlighted spans.
     * Input is user-provided text, so we escape first then highlight.
     */
    public static function render(string $text): string
    {
        $escaped = e($text);

        return preg_replace(
            '/@([A-Za-zÀ-ÿ]+(?:\s+[A-Za-zÀ-ÿ]+)?)/',
            '<span style="background:rgba(102,126,234,0.12); color:var(--primary); padding:0 3px; border-radius:3px; font-weight:500;">@$1</span>',
            $escaped
        );
    }
}
