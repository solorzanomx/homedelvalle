<?php

namespace App\Observers;

use App\Models\Post;

class PostObserver
{
    /**
     * Auto-inject alt text on any <img alt=""> inside the post body.
     * Runs before every save so it covers create AND update.
     */
    public function saving(Post $post): void
    {
        if (empty($post->body) || !str_contains($post->body, '<img')) {
            return;
        }

        // Only process when body actually changed
        if (!$post->isDirty('body') && $post->exists) {
            return;
        }

        $post->body = self::injectAltText($post->body, $post->title ?? '');
    }

    /**
     * Parse the body HTML and fill empty alt attributes.
     * Priority: figcaption text → post title (+ sequential counter for extra images).
     */
    private static function injectAltText(string $html, string $postTitle): string
    {
        $prev = libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) {
            return $html;
        }

        $counter = 0;
        foreach ($doc->getElementsByTagName('img') as $img) {
            $alt = trim($img->getAttribute('alt'));
            if ($alt !== '') {
                continue; // already has meaningful alt — leave it alone
            }

            $counter++;

            // Try figcaption of parent <figure>
            $caption = '';
            $parent = $img->parentNode;
            if ($parent instanceof \DOMElement && $parent->nodeName === 'figure') {
                foreach ($parent->childNodes as $child) {
                    if ($child instanceof \DOMElement && $child->nodeName === 'figcaption') {
                        $caption = trim($child->textContent);
                        break;
                    }
                }
            }

            $generated = $caption ?: (
                $counter === 1
                    ? $postTitle
                    : rtrim($postTitle, '.') . " — imagen {$counter}"
            );

            $img->setAttribute('alt', $generated);
        }

        // Reconstruct only the body content (no html/body wrappers)
        $result = '';
        foreach ($body->childNodes as $node) {
            $result .= $doc->saveHTML($node);
        }

        return $result ?: $html;
    }
}
