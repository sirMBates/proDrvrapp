<?php

declare(strict_types=1);

use App\Services\RegistrationService;
use App\Validation\Validator;
use App\Validation\UserValidator;
use Core\Flash;

class RegistrationContr {
    private int $userId;
    private string $operatorId;
    private string $firstName;
    private string $lastName;
    private string $mobileNumber;
    private string $birthDate;
    private string $cdlNumber;
    private string $cdlState;
    private string $cdlExpirationDate;
    private string $dotMedExpirationDate;
    private RegistrationService $registrationService;

    public function __construct(int $userId, string $operatorId, string $firstName, string $lastName, string $mobileNumber, string $birthDate, string $cdlNumber, string $cdlState, string $cdlExpirationDate, string $dotMedExpirationDate) {
        $this->userId = $userId;
        $this->operatorId = $operatorId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->mobileNumber = $mobileNumber;
        $this->birthDate = $birthDate;
        $this->cdlNumber = $cdlNumber;
        $this->cdlState = $cdlState;
        $this->cdlExpirationDate = $cdlExpirationDate;
        $this->dotMedExpirationDate = $dotMedExpirationDate;
        $this->registrationService = new RegistrationService();
    }

    public function processProfile(): void {
        if (!Validator::required($this->operatorId) || !Validator::required($this->firstName) || !Validator::required($this->lastName) || !Validator::required($this->mobileNumber) || !Validator::required($this->birthDate) || !Validator::required($this->cdlNumber) || !Validator::required($this->cdlState) || !Validator::required($this->cdlExpirationDate) || !Validator::required($this->dotMedExpirationDate)) {
            Flash::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /register?warning=missing+info"); //emptyinputs
            exit();
        }

        if (!UserValidator::name($this->firstName) || !UserValidator::name($this->lastName)) {
            Flash::setMsg('warning', 'Please re-enter your first or last name.');
            header("Location: /register?warning=invalid+name"); //FirstorLastnameNotValid
            exit();
        }

        if (!UserValidator::mobileNumber($this->mobileNumber)) {
            Flash::setMsg('warning', 'Please re-enter your mobile number.');
            header("Location: /register?warning=invalid+mobile"); //mobileNumberNotValid
            exit();
        }

        if (!Validator::date($this->birthDate)) {
            Flash::setMsg('warning', 'Please re-enter your date of birth.');
            header("Location: /register?warning=invalid+birthdate"); //birthdateNotValid
            exit();
        }

        if (!Validator::matches($this->operatorId, '/^[a-zA-Z0-9-]+$/')) {
            Flash::setMsg('danger', 'A problem occurred. Please try again.');
            header("Location: /register?danger=failed+operator+id");
            exit();
        }

        if (!Validator::matches($this->cdlNumber, '/^[A-Za-z0-9\- ]{1,32}$/')) {
            Flash::setMsg('warning', 'Please enter a valid driver license number.');
            header("Location: /register?warning=invalid+license");
            exit();
        }

        if (!Validator::matches($this->cdlState, '/^[A-Z]{2}$/')) {
            Flash::setMsg('warning', 'Please enter a valid issuing state.');
            header("Location: /register?warning=invalid+state");
            exit();
        }

        if (!Validator::currentOrFutureDate($this->cdlExpirationDate)) {
            Flash::setMsg('warning', 'Please enter a valid driver license expiration date.');
            header("Location: /register?warning=invalid+license+expiration");
            exit();
        }

        if (!Validator::currentOrFutureDate($this->dotMedExpirationDate)) {
            Flash::setMsg('warning', 'Please enter a valid DOT medical expiration date.');
            header("Location: /register?warning=invalid+medical+expiration");
            exit();
        }

        $updated = $this->registrationService->completeRegistration($this->userId, $this->operatorId, $this->firstName, $this->lastName, $this->mobileNumber, $this->birthDate, $this->cdlNumber, $this->cdlState, $this->cdlExpirationDate, $this->dotMedExpirationDate);

        if (!$updated) {
            Flash::setMsg('error', 'The registration could not be completed. Please try again.');
            header("Location: /register?error=try+again");
            exit();
        }
    }
}

?>