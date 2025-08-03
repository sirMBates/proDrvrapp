<?php

use core\Flash;

class AddedDrvr extends ConnectDatabase {
    protected function setDriver($username, $email, $password) {
        $alert = new Flash();
        $sql = "INSERT INTO driver (
                username, email, password, firstName, lastName, mobileNumber, birthdate) 
                VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->connect()->prepare($sql);

        $hashPsW = password_hash($password, PASSWORD_BCRYPT);
        $tmpFirstName = '';
        $tmpLastName = '';
        $tmpMobileNum = '';
        $tmpBirthDate = NULL;
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $hashPsW);
        $stmt->bindParam(4, $tmpFirstName);
        $stmt->bindParam(5, $tmpLastName);
        $stmt->bindParam(6, $tmpMobileNum);
        $stmt->bindParam(7, $tmpBirthDate);

        $result = $stmt->execute();

        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again"); //stmtfailed
            exit();
        }

        $token = '';
        $tokenExpTime = NULL;
        $sql2 = "INSERT INTO pwdreset (email, resetToken, tokenExpTime)
                VALUES (?,?,?)";
        $stmt2 = $this->connect()->prepare($sql2);
        $stmt2->bindParam(1, $email);
        $stmt2->bindParam(2, $token);
        $stmt2->bindParam(3, $tokenExpTime);
        
        $result2 = $stmt2->execute();

        if (!$result2) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again");  //stmtfailed
            exit();
        }
    }

    protected function checkDriver($username, $email) {
        $alert = new Flash();
        $sql = "SELECT driverid FROM driver
                WHERE username = ? OR email = ?";
        $stmt = $this->connect()->prepare($sql);
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $result = $stmt->execute();

        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again"); //stmtfailed
            exit();
        }

        $resultCheck;
        if ($stmt->rowCount() > 0) {
            $resultCheck = false;
        }
        else {
            $resultCheck = true;
        }
        return $resultCheck;
    }
}

?>