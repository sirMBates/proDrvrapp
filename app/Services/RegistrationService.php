<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DriverRepository;
use App\Repositories\DriverCredentialRepository;
use Core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class RegistrationService {
    public function completeRegistration(int $driverId, string $operatorId, string $firstName, string $lastName, string $mobileNumber, string $birthDate): bool {
        $pdo = (new Database())->connect();

        $driverRepository = new DriverRepository($pdo);
        $credentialRepository = new DriverCredentialRepository($pdo);

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        $encryptedFirstName = Crypto::encrypt($firstName, $key);
        $encryptedLastName = Crypto::encrypt($lastName, $key);
        $encryptedMobileNumber = Crypto::encrypt($mobileNumber, $key);
        $encryptedBirthDate = Crypto::encrypt($birthDate, $key);

        try {
            $pdo->beginTransaction();
            $userUpdated = $driverRepository->completeRegistration($driverId, $encryptedFirstName, $encryptedLastName, $encryptedMobileNumber, $encryptedBirthDate);

            if (!$userUpdated) {
                $pdo->rollBack();
                return false;
            }

            $credentialId = $driverCredentialRepository->create($driverId, $operatorId);
            if ($credentialId < 1) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }
}

?>