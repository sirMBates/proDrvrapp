<?php

use core\Database;
use core\Flash;

class ForgetPswd {
    protected function setForgetToken($token, $tokenExpTime, $email) {
        $db = new Database;
        $alert = new Flash(); 
        // Note: Can not use an INSERT stmt to add to an empty column of an existing row.
        $sql = "UPDATE pwd_reset
                SET reset_token = :reset_token, token_exp_time = :token_exp_time
                WHERE email = :email";
        $stmt = $db->connect()->prepare($sql);
        /* Bind the parameters to the prepared statement.
        *  The first two parameters are bound to the token and its expiration time.
        *  This allows for better readability and maintainability of the code.
        */
        $result = $stmt->execute([
            ':reset_token' => $token,
            ':token_exp_time' => $tokenExpTime,
            ':email' => $email
        ]);
        
        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /forget?error=try+again"); //stmtfailed
            exit();
        }
    }

    protected function checkEmailExist($email) {
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT email FROM pwd_reset 
                WHERE email = :email";
        $stmt = $db->connect()->prepare($sql);
        $result = $stmt->execute([
            ':email' => $email
        ]);

        $result->fetch();
        if (!$result || $result->rowCount() === 0) {
            $alert::setMsg('error', 'There seems to be a problem with server. Please try again!');
            header("Location: /forget?error=try+again");
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
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT reset_token FROM pwd_reset 
                WHERE email = :email";
        $stmt = $db->connect()->prepare($sql);

        $result = $stmt->execute([':email' => $email]);

        $doesTokenAlreadyExist = $stmt->fetch();
        $checkResult;
        //if ($doesTokenAlreadyExist && !empty($doesTokenAlreadyExist['resetToken'])) {
        if ($doesTokenAlreadyExist > 0 && !empty($doesTokenAlreadyExist['reset_token'])) {
            $checkResult = true;
        } else {
            $checkResult = false;
        }
        return $checkResult;
    }
}