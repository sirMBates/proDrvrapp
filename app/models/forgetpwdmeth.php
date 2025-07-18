<?php

use core\Flash;

class ForgetPswd extends ConnectDatabase {
    protected function setForgetToken($token, $tokenExpTime, $email) {
        $alert = new Flash(); 
        // Note: Can not use an INSERT stmt to add to an empty column of an existing row.
        $sql = "UPDATE pwdreset
                SET resetToken = :resetToken, tokenExpTime = :tokenExpTime
                WHERE drvr_email = :email";
        $stmt = $this->connect()->prepare($sql);
        /* Bind the parameters to the prepared statement.
        *  The first two parameters are bound to the token and its expiration time.
        *  This allows for better readability and maintainability of the code.
        */
        $result = $stmt->execute([
            ':resetToken' => $token,
            ':tokenExpTime' => $tokenExpTime,
            ':email' => $email
        ]);
        
        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /forget?error=try+again"); //stmtfailed
            exit();
        }
    }

    protected function checkEmailExist($email) {
        $alert = new Flash();
        $sql = "SELECT drvr_email FROM pwdreset 
                WHERE drvr_email = :email";
        $stmt =$this->connect()->prepare($sql);
        $result = $stmt->execute([
            ':email' => $email
        ]);
        if ($result === null) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /forget?error=try+again"); //stmtfailed
            exit();
        } 
        
        $resultCheck;
        if (!$result) {
            $resultCheck = false;
        } else {
            $resultCheck = true;
        }
        return $resultCheck;
    }

    protected function checkTokenExist($email) {
        $alert = new Flash();
        $sql = "SELECT resetToken FROM pwdreset 
                WHERE drvr_email = :email";
        $stmt = $this->connect()->prepare($sql);

        $result = $stmt->execute([':email' => $email]);
        if ($result === null) {
            $alert::setMsg('error', 'Sorry, something went wrong. try again.');
            header("Location: /forget?error=not+available"); //stmtfailed
            exit();
        }

        $checkResult;
        if ($doesTokenAlreadyExist && !empty($doesTokenAlreadyExist['resetToken'])) {
            $checkResult = true;
        } else {
            $checkResult = false;
        }
        return $checkResult;
    }
}