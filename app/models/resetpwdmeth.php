<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    protected function checkTokenExpiration($token_hash) {
        $alert = new Flash();
        $sql = "SELECT * From driver
                WHERE reset_token_hash = ?";                
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(1, $token_hash);

        if (!$stmt->execute(array($token_hash))) {
            $stmt = null;
            $alert::setMsg('info', 'Token not found. Please try again.');
            header("Location: /reset?info=try+again"); //stmtfailed
            exit();
        }

        $driver = $stmt->execute(array($tokenExpTime));
        $expired;
        if (strtotime($driver["token_exp_at"]) <= time()) {
            $expired = true;
        } else {
            $expired = false;
        }
        return $expired;
    }

    /*protected function getResetToken() {
        $alert = new Flash();
        $sql = "SELECT * FROM driver 
                WHERE reset_token_hash = ?";
        $stmt = $this->connect()->prepare($sql);

        $stmt->bindParam(1, $token_hash);

        if (!$stmt->execute(array($token_hash))) {
            $stmt = null;
            $alert::setMsg('error', 'There was a problem. Try again');
            header("Location: /forget?error=not+available"); //stmtfailed
            exit();
        }

        $result = $stmt->rowCount();
        $resultCheck;
        if ($result === 0) {
            $resultCheck = false;
        } else {
            $resultCheck = true;
        }
        return $resultCheck;
    }*/
}