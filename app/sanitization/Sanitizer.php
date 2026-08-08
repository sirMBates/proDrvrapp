<?php

namespace App\Sanitization;

final class Sanitizer {
    public static function plainText(string $value): string {
        $clean = strip_tags($value);
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
        return trim($clean);
    }
}

?>