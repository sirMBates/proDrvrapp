<?php

declare(strict_types=1);

use App\Validation\Validator;
use App\Validation\UserValidator;
use App\Repositories\DriverRepository;
use Core\Logger;
use Core\Flash;

class AddDrvrContr {
    private string $username;
    private string $email;
    private string $password;
    private DriverRepository $driverRepository;
    
    public function __construct(string $username, string $email, string $password) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->driverRepository = new DriverRepository();  
    }

    public function addDriver() {
        $alert = new Flash();
        if ($this->isEmpty()) {
            $alert::setMsg('warning', 'Please fill in all required fields.');
            header("Location: /signup?warning=empty"); //emptyinput
            exit();
        }

        if (!$this->isValidUsername()) {
            $alert::setMsg('warning', 'Please re-enter your username.');
            header("Location: /signup?warning=invalid"); //namenotvalid
            exit();
        }

        if (!$this->isValidEmail()) {
            $alert::setMsg('warning', 'Please re-enter your email.');
            header("Location: /signup?warning=invalid"); //emailnotvalid
            exit();
        }

        if (!$this->isValidPassword()) {
            $alert::setMsg('warning', 'Please re-enter your password.');
            header("Location: /signup?warning=invalid"); //passwordnotvalid
            exit();
        }

        if ($this->usernameOrEmailExists()) {
            $alert::setMsg('warning', 'Please choose a different username or email.');
            header("Location: /signup?warning=exist+already"); //nameexistalready
            exit();
        }
        
        try {
            $driverId = $this->driverRepository->createDriver($this->username, $this->email, $this->password);
            $_SESSION['driver_id'] = $driverId;
        } catch (\Throwable $exception) {
            $logger = new Logger(base_path('storage/logs/error.log'));
            $logger->error('[SIGNUP ERROR] ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again");
            exit();
        }
    }

    private function isEmpty(): bool {
        return !Validator::required($this->username) || !Validator::required($this->email) || !Validator::required($this->password);
    }

    private function isValidUsername(): bool {
        return UserValidator::username($this->username);
    }

    private function isValidEmail(): bool {
        return Validator::email($this->email);
    }

    private function isValidPassword(): bool {
        return UserValidator::password($this->password);
    }

    private function usernameOrEmailExists(): bool {
        return $this->driverRepository->checkDriver($this->username, $this->email);
    }
}
?>