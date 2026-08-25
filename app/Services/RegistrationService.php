<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DriverRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class RegistrationService {
    private DriverRepository $driverRepository;

    public function __construct() {
        $this->driverRepository = new DriverRepository();
    }

    public function completeRegistration(int $driverId, string $operatorId, string $firstName, string $lastName, string $mobileNumber, string $birthDate): bool {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        return $this->driverRepository->completeRegistration($driverId, $operatorId, Crypto::encrypt($firstName, $key), Crypto::encrypt($lastName, $key), Crypto::encrypt($mobileNumber, $key), Crypto::encrypt($birthDate, $key));
    }
}

?>