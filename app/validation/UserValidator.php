<?php

namespace App\Validation;

class UserValidator {
    public static function username(string $username): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{4,}$/', $username) === 1;
    }

    public static function password(string $password): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#%&_]).\S{7,}$/', $password) === 1;
    }
}

?>