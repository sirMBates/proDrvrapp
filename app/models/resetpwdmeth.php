<?php

use core\Flash;

class ResetPwd extends ConnectDatabase {
    protected function checkTokenExpiration($token) {
        $alert = new Flash();
        $sql = "SELECT * From driver
                WHERE reset_token_hash = ?";                
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(1, $token);

        if (!$stmt->execute(array($token))) {
            $stmt = null;
            $alert::setMsg('error', 'Token not found! Please generate a token to reset password.');
            header("Location: /reset?error=not+found"); //stmtfailed
            exit();
        }

        $driver = $stmt->execute(array($token));
        $expired;
        if (strtotime($driver["token_exp_at"]) <= time()) {
            $expired = true;
        } else {
            $expired = false;
        }
        return $expired;
    }

    protected function checkEmailExist($token) {
        $alert = new Flash();
        $sql = "SELECT email FROM driver 
                WHERE reset_token_hash = ?";
        $stmt = $this->connect()->prepare($sql);

        $stmt->bindParam(1, $token);

        if (!$stmt->execute(array($token))) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /reset?error=try+again"); //stmtfailed
            exit();
        } 

        $resultCheck;
        if ($stmt->rowCount() === 0) {
            $resultCheck = false;
        } else {
            $resultCheck = true;
        }
        return $resultCheck;
    }

    protected function getNewToken($token, $tokenExpTime) {
        $alert = new Flash();
        $sql = "UPDATE driver
                SET reset_token_hash = ?, token_exp_at = ? 
                WHERE reset_token_hash = ?";
        $stmt = $this->connect()->prepare($sql);

        $stmt->bindParam(1, $token);
        $stmt->bindParam(2, $tokenExpTime);

        if (!$stmt->execute(array($token, $tokenExpTime))) {
            $stmt = null;
            $alert::setMsg('error', 'There was a problem, Try again!');
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
    }
}