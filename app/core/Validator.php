<?php

namespace App\Core;

class Validator {

    public static function string(string $value, int $min, int $max): bool
    {
        $valueLength = strlen($value);
        return $valueLength >= $min && $valueLength <= $max;
    }

    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function int(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
}