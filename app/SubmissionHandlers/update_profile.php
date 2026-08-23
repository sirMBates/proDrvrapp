<?php

declare(strict_types=1);

use App\Services\ProfileAccountService;
use App\Validation\Validator;
use App\Validation\UserValidator;
use Core\Flash;

class UpdateDrvrContr {
    private int $driverId;
    private ?string $password;
    private ?string $email;
    private ?string $phoneNumber;
    private ProfileAccountService $profileAccountService;

    public function __construct(int $driverId, ?string $password, ?string $email, ?string $phoneNumber) {
        $this->driverId = $driverId;
        $this->password = $password;  
        $this->email = $email;  
        $this->phoneNumber = $phoneNumber;
        $this->profileAccountService = new ProfileAccountService();  
    }

    public function changeDriverPassword(): void {
        if ($this->driverId < 1) {
            Flash::setMsg('danger', 'You must be logged into your account for this change.');
            header("location: /profile?danger=not+authorized");
            exit();
        }

        if (!Validator::required($this->password)) {
            Flash::setMsg('warning', 'Please enter your new password.');
            header("Location: /profile?warning=empty");
            exit();
        }

        if (!UserValidator::password($this->password)) {
            Flash::setMsg('warning', 'Please re-type your password.');
            header("Location: /profile?warning=check+password");
            exit();
        }

        $updated = $this->profileAccountService->updatePassword($this->driverId, $this->password);
        if (!$updated) {
            Flash::setMsg('error', 'The request was not completed. Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }
    }

    public function updateContactInformation(): void {
        if (!Validator::required($this->email) && !Validator::required($this->phoneNumber)) {
            Flash::setMsg('warning', 'Please enter an email address or mobile number.');
            header("Location: /profile?warning=empty");
            exit();
        }

        if (Validator::required($this->email) && !Validator::email($this->email)) {
            Flash::setMsg('warning', 'Please enter a valid email address.');
            header("Location: /profile?warning=invalid+email");
            exit();
        }

        if (Validator::required($this->email) && $this->profileAccountService->emailExistsForOtherDriver($this->driverId, $this->email)) {
            Flash::setMsg('warning', 'That email address is already in use.');
            header("Location: /profile?warning=email+exists");
            exit();
        }

        if (Validator::required($this->phoneNumber) && !UserValidator::mobileNumber($this->phoneNumber)) {
            Flash::setMsg('warning', 'Please enter a valid mobile number.');
            header("Location: /profile?warning=invalid+number");
            exit();
        }
        
        $updated = $this->profileAccountService->updateContactInformation($this->driverId, $this->email, $this->phoneNumber);
        if (!$updated) {
            Flash::setMsg('error', 'The request was not completed. Please try again.');
            header("Location: /profile?error=try+again");
            exit();
        }
    }
}

?>