<?php

use core\Flash;

class CompleteResetContr extends CompleteReset {
    private $password;
    private $token;

    public function __construct($password, $token) {
        $this->password = $password;
        $this->token = $token;
    }

    public function isTokenCleared() {}
}