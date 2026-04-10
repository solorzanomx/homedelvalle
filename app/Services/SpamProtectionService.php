<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SpamProtectionService
{
    /**
     * Verify reCAPTCHA v3 token.
     * Returns score (0.0 = bot, 1.0 = human). Returns 0.0 on failure.
     */
    public function verifyRecaptcha(?string $token, string $expectedAction = 'contact'): float
    {
        $secret = config('services.recaptcha.secret');

        if (! $secret || ! $token) {
            // If not configured, skip (allows dev without keys)
            return $secret ? 0.0 : 1.0;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
            ]);

            $body = $response->json();

            if (! ($body['success'] ?? false)) {
                return 0.0;
            }

            // Verify action matches to prevent token reuse across forms
            if (($body['action'] ?? '') !== $expectedAction) {
                return 0.0;
            }

            return (float) ($body['score'] ?? 0.0);
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification failed', ['error' => $e->getMessage()]);
            return 0.5; // Fail open on network errors to not block real users
        }
    }

    /**
     * Check if reCAPTCHA score passes the threshold.
     */
    public function passesRecaptcha(?string $token, string $action = 'contact'): bool
    {
        $threshold = config('services.recaptcha.threshold', 0.5);

        return $this->verifyRecaptcha($token, $action) >= $threshold;
    }

    /**
     * Detect spam patterns in text content.
     * Returns true if the text looks like spam.
     */
    public function isSpamContent(string $text): bool
    {
        if (empty(trim($text))) {
            return false;
        }

        // 1. Excessive URLs (more than 2)
        if (preg_match_all('/https?:\/\/|www\./i', $text) > 2) {
            return true;
        }

        // 2. Common spam keywords (English spam on a Spanish site is suspicious)
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|slots|bitcoin|crypto|forex|SEO|backlink|buy\s+now|click\s+here|free\s+money|earn\s+\$|make\s+money|work\s+from\s+home)\b/i',
            '/\b(cheap\s+\w+\s+online|order\s+now|limited\s+offer|act\s+now|congratulations\s+you\s+won)\b/i',
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        // 3. Gibberish detection: ratio of consonant clusters
        $consonantClusters = preg_match_all('/[bcdfghjklmnpqrstvwxyz]{5,}/i', $text);
        if ($consonantClusters >= 3) {
            return true;
        }

        // 4. Too many repeated characters (e.g., "aaaaaaa" or "xyzxyzxyz")
        if (preg_match('/(.)\1{7,}/', $text)) {
            return true;
        }

        // 5. Excessive special characters ratio
        $specialCount = preg_match_all('/[^a-záéíóúüñ\s.,;:!?¿¡()\-\d]/iu', $text);
        $totalChars = mb_strlen($text);
        if ($totalChars > 10 && ($specialCount / $totalChars) > 0.3) {
            return true;
        }

        // 6. All caps message (longer than 20 chars)
        if (mb_strlen($text) > 20 && $text === mb_strtoupper($text) && preg_match('/[A-ZÁÉÍÓÚÜÑa-z]/', $text)) {
            return true;
        }

        // 7. Repeated words/phrases pattern (bot-generated text)
        $words = preg_split('/\s+/', mb_strtolower($text));
        if (count($words) >= 6) {
            $uniqueRatio = count(array_unique($words)) / count($words);
            if ($uniqueRatio < 0.3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that an email doesn't use a disposable/temporary domain.
     */
    public function isDisposableEmail(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@'), 1));

        $disposable = [
            'mailinator.com', 'guerrillamail.com', 'tempmail.com', 'throwaway.email',
            'yopmail.com', 'sharklasers.com', 'guerrillamailblock.com', 'grr.la',
            'dispostable.com', 'mailnesia.com', 'maildrop.cc', 'fakeinbox.com',
            'trashmail.com', 'tempail.com', 'temp-mail.org', '10minutemail.com',
            'mohmal.com', 'getnada.com', 'emailondeck.com', 'crazymailing.com',
        ];

        return in_array($domain, $disposable, true);
    }

    /**
     * Check if IP has submitted too many times recently (cache-based).
     */
    public function hasExcessiveSubmissions(string $ip, int $maxPerHour = 5): bool
    {
        $key = 'form_submissions:' . md5($ip);
        $count = (int) Cache::get($key, 0);

        return $count >= $maxPerHour;
    }

    /**
     * Increment submission counter for an IP.
     */
    public function recordSubmission(string $ip): void
    {
        $key = 'form_submissions:' . md5($ip);
        $count = (int) Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->addHour());
    }

    /**
     * Run all spam checks. Returns array with pass/fail and reason.
     */
    public function check(array $data, ?string $recaptchaToken, string $ip, string $action = 'contact'): array
    {
        // 1. reCAPTCHA
        if (! $this->passesRecaptcha($recaptchaToken, $action)) {
            Log::info('Spam blocked: reCAPTCHA failed', ['ip' => $ip]);
            return ['pass' => false, 'reason' => 'recaptcha'];
        }

        // 2. IP rate (cache-based additional layer)
        if ($this->hasExcessiveSubmissions($ip)) {
            Log::info('Spam blocked: excessive submissions', ['ip' => $ip]);
            return ['pass' => false, 'reason' => 'rate_limit'];
        }

        // 3. Disposable email
        if (! empty($data['email']) && $this->isDisposableEmail($data['email'])) {
            Log::info('Spam blocked: disposable email', ['ip' => $ip, 'email' => $data['email']]);
            return ['pass' => false, 'reason' => 'disposable_email'];
        }

        // 4. Spam content in message
        $textFields = array_filter([
            $data['message'] ?? null,
            $data['name'] ?? null,
        ]);

        foreach ($textFields as $text) {
            if ($this->isSpamContent($text)) {
                Log::info('Spam blocked: content analysis', ['ip' => $ip, 'text' => mb_substr($text, 0, 100)]);
                return ['pass' => false, 'reason' => 'spam_content'];
            }
        }

        // All checks passed
        $this->recordSubmission($ip);

        return ['pass' => true, 'reason' => null];
    }
}
