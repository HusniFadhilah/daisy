<?php
// ============================================================
// BUAT FILE BARU: app/Rules/MaxPlainTextLength.php
// php artisan make:rule MaxPlainTextLength  (lalu ganti isinya)
// ============================================================

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validasi panjang teks setelah HTML tag di-strip.
 * Cocok untuk value TinyMCE yang berisi markup HTML.
 */
class MaxPlainTextLength implements ValidationRule
{
    public function __construct(protected int $max) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Normalise: decode HTML entities, strip tags, trim whitespace berlebih
        $plain = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/\s+/', ' ', $plain);
        $plain = trim($plain);

        if (mb_strlen($plain) > $this->max) {
            $fail("Teks tidak boleh melebihi {$this->max} karakter (saat ini " . mb_strlen($plain) . " karakter).");
        }
    }
}
