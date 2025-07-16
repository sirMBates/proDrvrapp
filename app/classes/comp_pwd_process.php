<?php

use core\Flash;

class CompPwdProcessContr extends CompPwdProcess {
    private $password;

    public __construct($password) {
        $this->password = $password;
    }
}