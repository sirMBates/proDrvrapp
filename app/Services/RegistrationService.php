<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\DriverCredentialRepository;
use Core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class RegistrationService {
    public function completeRegistration(int $userId, string $operatorId, string $firstName, string $lastName, string $mobileNumber, string $birthDate, string $cdlNumber, string $cdlState, string $cdlExpirationDate, string $dotMedExpirationDate): bool {
        $pdo = (new Database())->connect();

        $userRepository = new UserRepository($pdo);
        $driverCredentialRepository = new DriverCredentialRepository($pdo);

        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

        $encryptedFirstName = Crypto::encrypt($firstName, $key);
        $encryptedLastName = Crypto::encrypt($lastName, $key);
        $encryptedMobileNumber = Crypto::encrypt($mobileNumber, $key);
        $encryptedBirthDate = Crypto::encrypt($birthDate, $key);
        $encryptedCdlNumber = Crypto::encrypt($cdlNumber, $key);

        try {
            $pdo->beginTransaction();
            $userUpdated = $userRepository->completeRegistration($userId, $encryptedFirstName, $encryptedLastName, $encryptedMobileNumber, $encryptedBirthDate);

            if (!$userUpdated) {
                $pdo->rollBack();
                return false;
            }

            $credentialId = $driverCredentialRepository->create($userId, $operatorId, $encryptedCdlNumber, $cdlState, $cdlExpirationDate, $dotMedExpirationDate);
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