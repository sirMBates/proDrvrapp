<?php

$alert = new core\Flash();

class Logincontr extends Login {
    private $username;
    private $password;
    

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function loginDriver() {
        if ($this->isEmpty() === false) {
            $alert::setMsg('error', 'Please fill in all fields.');
            header("Location: /signin?error=empty"); // emptyInputs
            exit();
        }
        $this->getDriver($this->username, $this->password);
    }

    private function isEmpty() {
        $result;
        if (empty($this->username) || empty($this->password)) {
            $result = false;
        }
        else {
            $result = true;
        }
        return $result;
    }
}
?>