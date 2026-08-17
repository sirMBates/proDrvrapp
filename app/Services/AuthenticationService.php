<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DriverRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class AuthenticationService {
    private DriverRepository $driverRepository;

    public function __construct() {
        $this->driverRepository = new DriverRepository();
    }

    public function authenticate(string $username, string $password): ?array {
        $driver = $this->driverRepository->findByUsername($username);

        if ($driver === null) {
            return null;
        }

        if (!password_verify($password, $driver['password'])) {
            return [
                'authenticated' => false,
                'reason' => 'invalid_password'
            ];
        }

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        return [
            'authenticated' => true,
            'driverId' => (int) $driver['driver_id'],
            'firstName' => Crypto::decrypt($driver['first_name'], $key),
            'birthdate' => Crypto::decrypt($driver['birth_date'], $key)
        ];
    }
}

?>