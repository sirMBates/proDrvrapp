<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\DriverCredentialRepository;
use App\Validation\Validator;
use App\Validation\UserValidator;
use Core\Database;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class RegistrationService {
    public function completeRegistration(int $userId, string $firstName, string $lastName, string $mobileNumber, string $birthDate, ?string $cdlNumber = null, ?string $cdlState = null, ?string $cdlExpirationDate = null, ?string $dotMedExpirationDate = null): bool {
        $pdo = (new Database())->connect();

        $userRepository = new UserRepository($pdo);
        $driverCredentialRepository = new DriverCredentialRepository($pdo);
        $staffIdService = new StaffIdService($pdo);

        try {
            // The role stored on the user account is authoritative.
            $user = $userRepository->findById($userId);
            if (($user['account_status'] ?? null) !== 'pending') {
                throw new \InvalidArgumentException('This account is not eligible to complete registration.');
            }

            $role = (string) $user['role'];

            if (!in_array($role, ['driver', 'dispatch', 'admin'], true)) {
                throw new \RuntimeException('Invalid registration role.');
            }

            $this->validateCommonProfile($firstName, $lastName, $mobileNumber, $birthDate);

            if ($role === 'driver') {
                $this->validateDriverCredentials($cdlNumber, $cdlState, $cdlExpirationDate, $dotMedExpirationDate);
            }

            $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);

            $encryptedFirstName = Crypto::encrypt($firstName, $key);
            $encryptedLastName = Crypto::encrypt($lastName, $key);

            $encryptedMobileNumber = Crypto::encrypt($mobileNumber, $key);
            $encryptedBirthDate = Crypto::encrypt($birthDate, $key);
            $encryptedCdlNumber = null;

            if ($role === 'driver') {
                if ($cdlNumber === null || $cdlState === null || $cdlExpirationDate === null || $dotMedExpirationDate === null) {
                    throw new \RuntimeException('Driver credential information is required.');
                }

                $encryptedCdlNumber = Crypto::encrypt($cdlNumber, $key);
            }

            $pdo->beginTransaction();

            $staffId = $staffIdService->allocate($role);

            $staffIdAssigned = $userRepository->assignStaffId($userId, $staffId);
            if (!$staffIdAssigned) {
                throw new \RuntimeException('Unable to assign staff ID.');
            }

            $userUpdated = $userRepository->completeRegistration($userId, $encryptedFirstName, $encryptedLastName, $encryptedMobileNumber, $encryptedBirthDate);
            if (!$userUpdated) {
                throw new \RuntimeException('Unable to complete user registration.');
            }

            if ($role === 'driver') {
                $credentialId = $driverCredentialRepository->create($userId, $encryptedCdlNumber, $cdlState, $cdlExpirationDate, $dotMedExpirationDate);
                if ($credentialId < 1) {
                    throw new \RuntimeException('Unable to create driver credentials.');
                }
            }

            // Activate only after all onboarding operations succeed.
            $accountActivated = $userRepository->activatePendingAccount($userId);

            if (!$accountActivated) {
                throw new \RuntimeException('Unable to activate account after registration.');
            }

            $pdo->commit();
            return true;
        } catch (\InvalidArgumentException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    private function validateCommonProfile(string $firstName, string $lastName, string $mobileNumber, string $birthDate): void {
        if (!Validator::required($firstName) || !Validator::required($lastName) || !Validator::required($mobileNumber) || !Validator::required($birthDate)) {
            throw new \InvalidArgumentException('Please fill in all required fields.');
        }

        if (!UserValidator::name($firstName) || !UserValidator::name($lastName)) {
            throw new \InvalidArgumentException('Please re-enter your first or last name.');
        }

        if (!UserValidator::mobileNumber($mobileNumber)) {
            throw new \InvalidArgumentException('Please re-enter your mobile number.');
        }

        if (!Validator::date($birthDate)) {
            throw new \InvalidArgumentException('Please re-enter your date of birth.');
        }
    }

    private function validateDriverCredentials(?string $cdlNumber, ?string $cdlState, ?string $cdlExpirationDate, ?string $dotMedExpirationDate): void {
        if ($cdlNumber === null || $cdlState === null || $cdlExpirationDate === null || $dotMedExpirationDate === null || !Validator::required($cdlNumber) || !Validator::required($cdlState) || !Validator::required($cdlExpirationDate) || !Validator::required($dotMedExpirationDate)) {
            throw new \InvalidArgumentException('Please fill in all required driver credential fields.');
        }

        if (!Validator::matches($cdlNumber, '/^[A-Za-z0-9\- ]{1,32}$/')) {
            throw new \InvalidArgumentException('Please enter a valid driver license number.');
        }

        if (!Validator::matches($cdlState, '/^[A-Z]{2}$/')) {
            throw new \InvalidArgumentException('Please enter a valid issuing state.');
        }

        if (!Validator::currentOrFutureDate($cdlExpirationDate)) {
            throw new \InvalidArgumentException('Please enter a valid driver license expiration date.');
        }

        if (!Validator::currentOrFutureDate($dotMedExpirationDate)) {
            throw new \InvalidArgumentException('Please enter a valid DOT medical expiration date.');
        }
    }
}

?>