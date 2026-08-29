<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\DriverCredentialRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class DriverProfileService {
    private UserRepository $userRepository;
    private DriverCredentialRepository $driverCredentialRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->driverCredentialRepository = new DriverCredentialRepository();
    }

    public function driverProfile(int $userId): array {
        $user = $this->userRepository->findById($userId);
        if ($user['role'] !== 'driver') {
            throw new \RuntimeException('User is not a driver.');
        }

        $credentials = $this->driverCredentialRepository->findByUserId($userId);
        if ($credentials === null) {
            throw new \RuntimeException('Driver credentials not found.');
        }

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        return [
            'userId' => (int) $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'operatorid' => $credentials['operator_id'],

            'firstName' => Crypto::decrypt($user['first_name'], $key),
            'lastName' => Crypto::decrypt($user['last_name'], $key),
            'mobileNumber' => Crypto::decrypt($user['mobile_number'], $key),
            'birthdate' => Crypto::decrypt($user['birth_date'], $key),
            'profilePicture' => $user['profile_picture'] ?: null
        ];
    }
}


?>