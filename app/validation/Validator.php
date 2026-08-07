<?php

declare(strict_types=1);

namespace App\Validation;

final class Validator {
    public const DEFAULT_TEXT_LENGTH = 300;

    public static function required(mixed $value): bool {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    public static function positiveInteger(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    public static function nonNegativeInteger(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) !== false;
    }

    public static function integer(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function nonNegativeDecimal(mixed $value): bool {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return false;
        }

        return (float) $value >= 0;
    }

    public static function optionalNonNegativeDecimal(mixed $value): bool {
        if (!self::required($value)) {
            return true;
        }

        return self::nonNegativeDecimal($value);
    }

    public static function decimalPlaces(mixed $value, int $maximumPlaces = 2): bool {
        if (!self::nonNegativeDecimal($value) || $maximumPlaces < 0) {
            return false;
        }

        $value = trim((string) $value);
        if ($maximumPlaces === 0) {
            return preg_match('/^\d+$/', $value) === 1;
        }

        return preg_match('/^\d+(?:\.\d{1,' . $maximumPlaces . '})?$/', $value) === 1;
    }

    public static function assignmentControl(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^PD-\d{8}-\d{6}-[A-F0-9]{4}-\d{4}$/', trim($value)) === 1;
    }

    public static function email(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function textLength(mixed $value, int $minimum = 0, int $maximum = self::DEFAULT_TEXT_LENGTH): bool {
        if (!is_string($value) || $minimum < 0 || $maximum < $minimum) {
            return false;
        }

        $length = mb_strlen(trim($value));
        return ($length >= $minimum && $length <= $maximum);
    }

    public static function optionalTextLength(mixed $value, int $maximum = self::DEFAULT_TEXT_LENGTH): bool {
        if (!self::required($value)) {
            return true;
        }

        return self::textLength($value, 0, $maximum);
    }

    public static function time(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);
        foreach (['H:i', 'H:i:s'] as $format) {
            if (self::matchesDateFormat($value, $format)) {
                return true;
            }
        }

        return false;
    }

    public static function optionalTime(mixed $value): bool {
        if (!self::required($value)) {
            return true;
        }

        return self::time($value);
    }

    public static function date(mixed $value): bool {
        return is_string($value) && self::matchesDateFormat(trim($value), 'Y-m-d');
    }

    public static function dateTime(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);
        $formats = [
            'Y-m-d H:i',
            'Y-m-d H:i:s',
            'Y-m-d\TH:i',
            'Y-m-d\TH:i:s'
        ];

        foreach ($formats as $format) {
            if (self::matchesDateFormat($value, $format)) {
                return true;
            }
        }

        return false;
    }

    public static function optionalDateTime(mixed $value): bool {
        if (!self::required($value)) {
            return true;
        }

        return self::dateTime($value);
    }

    public static function oneOf(mixed $value, array $allowedValues, bool $strict = true): bool {
        return in_array($value, $allowedValues, $strict);
    }

    public static function imageDataUrl(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        $value = trim($value);
        return preg_match('/^data:image\/(?:png|jpeg|jpg|webp);base64,' . '[A-Za-z0-9+\/]+={0,2}$/', $value) === 1;
    }

    public static function optionalImageDataUrl(mixed $value): bool {
        if (!self::required($value)) {
            return true;
        }

        return self::imageDataUrl($value);
    }

    public static function matches(mixed $value, string $pattern): bool {
        if (!is_string($value)) {
            return false;
        }

        return preg_match($pattern, trim($value)) === 1;
    }

    private static function matchesDateFormat(string $value, string $format): bool {
        $date = \DateTimeImmutable::createFromFormat('!' . $format, $value);

        if ($date === false) {
            return false;
        }

        $errors = \DateTimeImmutable::getLastErrors();

        if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return false;
        }

        return $date->format($format) === $value;
    }
}
?>