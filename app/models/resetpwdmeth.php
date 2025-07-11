<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    protected function getResetToken($token_hash) {
        $alert = new Flash();
        $sql = "SELECT * FROM driver 
                WHERE reset_token_hash = ?";
        $stmt = $this->connect()->prepare($sql);

        $stmt->bindParam(1, $token_hash);

        if (!$stmt->execute(array($token_hash))) {
            $stmt = null;
            $alert::setMsg('error', 'No token found. Please try again.');
            header("Location: /reset?error=try+again"); //stmtfailed
            exit();
        }
    }

    protected function tokenExpiration($tokenExp) {
        $alert = new Flash();
        if (strtotime($driver['token_exp_at']) <= time()) {
            $alert::setMsg
        }
    }
}