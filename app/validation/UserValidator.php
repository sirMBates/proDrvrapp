<?php

declare(strict_types=1);

namespace App\Validation;

class UserValidator {
    public static function username(string $username): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{5,}$/', $username) === 1;
    }

    public static function password(string $password): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#%&$_]).\S{8,}$/', $password) === 1;
    }

    public static function mobileNumber(string $mobileNumber): bool {
        return preg_match('/^[0-9]{10}$/', $mobileNumber) === 1;
    }
}

?>