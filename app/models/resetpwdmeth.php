<?php

use core\Flash;

class ResetPswd extends ConnectDatabase {
    protected function setResetToken($token_hash, $tokenExpTime, $email) {
        $alert = new Flash();
        // Prepare the SQL statement to update the reset token and its expiration time.
        // Note: The SQL syntax is corrected to use the SET clause properly.
        $sql = "UPDATE driver 
                SET reset_token_hash = ?, token_exp_at = ?
                WHERE email = ?";
        $stmt = $this->connect()->prepare($sql);
        /* Bind the parameters to the prepared statement.
        *  The first two parameters are bound to the token and its expiration time.
        *  The third parameter is bound to the email.
        *  Note: The email parameter is bound using a named placeholder (:email).
        *  This allows for better readability and maintainability of the code.
        */
        $stmt->bindParam(1, $token_hash);
        $stmt->bindParam(2, $tokenExpTime);
        $stmt->bindParam(3, $email);
        
        if (!$stmt->execute(array($token_hash, $tokenExpTime, $email))) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /reset?error=try+again"); //stmtfailed
            exit();
        }

        $stmt = null;
    }

    protected function checkEmailExist($email) {
        $alert = new Flash();
        $stmt =$this->connect()->prepare('SELECT email FROM driver WHERE email = ?');
        if (!$stmt->execute(array($email))) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /reset?error=try+again"); //stmtfailed
            exit();
        } 

        $resultCheck;
        if ($stmt->rowCount() > 0) {
            $resultCheck = false;
        } else {
            $resultCheck = true;
        }
        return $resultCheck;
    }
}