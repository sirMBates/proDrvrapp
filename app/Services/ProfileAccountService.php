<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DriverRepository;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class ProfileAccountService {
    private DriverRepository $driverRepository;
    private AuthenticationService $authenticationService;

    public function __construct() {
        $this->driverRepository = new DriverRepository();
        $this->authenticationService = new AuthenticationService();
    }

    public function updatePassword(int $driverId, string $currentPassword, string $newPassword): bool {
        $verified = $this->authenticationService->verifyPasswordByDriverId($driverId, $currentPassword);
        if (!$verified) {
            return false;
        }

        return $this->driverRepository->updatePassword($driverId, $newPassword);
    }

    public function emailExistsForOtherDriver(int $driverId, string $email): bool {
        return $this->driverRepository->emailExistsForOtherDriver($driverId, $email);
    }

    public function updateContactInformation(int $driverId, ?string $email, ?string $mobile): bool {
        $encryptedMobile = null;

        if ($mobile !== null && $mobile !== '') {
            $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
            $encryptedMobile = Crypto::encrypt($mobile, $key);
        }

        return $this->driverRepository->updateContactInformation($driverId, $email, $encryptedMobile);
    }
}

?>