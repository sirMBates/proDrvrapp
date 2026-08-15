<?php

namespace App\Validation;

class UserValidator {
    public static function username(string $username): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).\S{5,}$/', $username) === 1;
    }

    public static function password(string $password): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#%&$_]).\S{8,}$/', $password) === 1;
    }
}

?>