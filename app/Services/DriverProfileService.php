<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DriverRepository;
use App\Repositories\DriverCredentialRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class DriverProfileService {
    private DriverRepository $driverRepository;
    private DriverCredentialRepository $driverCredentialRepository;

    public function __construct() {
        $this->driverRepository = new DriverRepository();
        $this->driverCredentialRepository = new DriverCredentialRepository();
    }

    public function driverProfile(int $driverId): array {
        $driver = $this->driverRepository->findById($driverId);
        $credentials = $this->driverCredentialRepository->findByUserId($driverId);
        if ($credentials === null) {
            throw new \RuntimeException('Driver credentials not found.');
        }

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        return [
            'driverId' => (int) $driver['driver_id'],
            'username' => $driver['username'],
            'email' => $driver['email'],
            'operatorid' => $credentials['operator_id'],

            'firstName' => Crypto::decrypt($driver['first_name'], $key),
            'lastName' => Crypto::decrypt($driver['last_name'], $key),
            'mobileNumber' => Crypto::decrypt($driver['mobile_number'], $key),
            'birthdate' => Crypto::decrypt($driver['birth_date'], $key),
            'profilePicture' => $driver['profile_picture'] ?: null
        ];
    }
}


?>