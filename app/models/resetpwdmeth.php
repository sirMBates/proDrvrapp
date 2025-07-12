<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    

    protected function tokenExpiration($tokenExp) {
        $alert = new Flash();
        if (strtotime($driver['token_exp_at']) <= time()) {
            $alert::setMsg('info', 'Token has expired. Request for a new token.');
            header("Location: /forget?info=expired");
            exit();
        }
    }
}