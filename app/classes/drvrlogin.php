<?php
class Logincontr extends Login {
    private $username;
    private $password;
    

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function loginDriver() {
        if ($this->isEmpty() === false) {
            //echo "<p class='text-capitalize fs-3'>empty input</p>";
            header("Location: /signin?error=emptyinput");
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