<?php

declare(strict_types=1);

use App\Services\AuthenticationService;
use App\Validation\Validator;
use Core\Flash;

class Logincontr {
    private string $username;
    private string $password;
    private AuthenticationService $authenticationService;    

    public function __construct(string $username, string $password) {
        $this->username = $username;
        $this->password = $password;
        $this->authenticationService = new AuthenticationService();
    }

    public function loginDriver(): void {
        if ($this->isEmpty()) {
            Flash::setMsg('error', 'Please fill in all fields.');
            header("Location: /signin?error=empty"); // emptyInputs
            exit();
        }
        $result = $this->authenticationService->authenticate($this->username, $this->password);

        if ($result === null) {
            Flash::setMsg('error', 'User not found. Please check your username.');
            header("Location: /signin?error=not+found");
            exit();
        }

        if (!$result['authenticated']) {
            Flash::setMsg('danger', 'Incorrect password. Please try again.');
            header("Location: /signin?danger=invalid");
            exit();
        }
        session_regenerate_id(true);

        $_SESSION['driver_id'] = $result['driverId'];
        $_SESSION['first_name'] = $result['firstName'];
        $_SESSION['logged_in'] = true;
        $birthdate = $result['birthdate'];

        if (!empty($birthdate) && date('md') === date('md', strtotime($birthdate))) {
            $_SESSION['birth_date'] = $birthdate;
        }
    }

    private function isEmpty(): bool {
        return !Validator::required($this->username) || !Validator::required($this->password);        
    }
}

?>