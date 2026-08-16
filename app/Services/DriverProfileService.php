<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DriverRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class DriverProfileService {
    private DriverRepository $driverRepository;

    public function __construct() {
        $this->driverRepository = new DriverRepository();
    }

    public function driverProfile(int $driverId): array {
        $driver = $this->driverRepository->findById($driverId);
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        return [
            'driverId' => (int) $driver['driver_id'],
            'username' => $driver['username'],
            'email' => $driver['email'],
            'operatorid' => $driver['operator_id'],

            'firstName' => Crypto::decrypt($driver['first_name'], $key),
            'lastName' => Crypto::decrypt($driver['last_name'], $key),
            'mobileNumber' => Crypto::decrypt($driver['mobile_number'], $key),
            'birthdate' => Crypto::decrypt($driver['birth_date'], $key),
            'profilePicture' => $driver['profile_picture'] ?: null
        ];
    }
}


?>