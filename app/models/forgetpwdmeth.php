<?php

use core\Flash;

class ForgetPswd extends ConnectDatabase {
    protected function setForgetToken($token_hash, $tokenExpTime, $email) {
        $alert = new Flash(); 
        // Note: Can not use an INSERT stmt to add to an empty column of an existing row.
        $sql = "UPDATE driver
                SET resetToken = :resetToken, tokenExpTime = :tokenExpTime
                WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);
        /* Bind the parameters to the prepared statement.
        *  The first two parameters are bound to the token and its expiration time.
        *  This allows for better readability and maintainability of the code.
        */
        $result = $stmt->execute([
            ':resetToken' => $token_hash,
            ':tokenExpTime' => $tokenExpTime,
            ':email' => $email
        ]);
        
        if (!$result) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /forget?error=try+again"); //stmtfailed
            exit();
        }
        $stmt = null;
    }

    protected function checkEmailExist($email) {
        $alert = new Flash();
        $sql = "SELECT email FROM driver 
                WHERE email = ?";
        $stmt =$this->connect()->prepare($sql);
        if (!$stmt->execute([$email])) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /forget?error=try+again"); //stmtfailed
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

    protected function checkTokenExist($email) {
        $alert = new Flash();
        $sql = "SELECT resetToken FROM driver 
                WHERE email = :email";
        $stmt = $this->connect()->prepare($sql);

        $stmt->bindParam(':email', $email);
        if (!$stmt->execute([':email' => $email])) {
            $stmt = null;
            $alert::setMsg('error', 'Sorry, something went wrong. try again.');
            header("Location: /forget?error=not+available"); //stmtfailed
            exit();
        }

        $doesTokenAlreadyExist = $stmt->fetch();
        $result;
        if ($doesTokenAlreadyExist && !empty($doesTokenAlreadyExist['resetToken'])) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}