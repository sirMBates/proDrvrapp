<?php

use core\Flash;

class AddedDrvr extends ConnectDatabase {
    $alert = new Flash();
    protected function setDriver($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate) {
        $stmt = $this->connect()->prepare("INSERT INTO driver (username, email, password, firstName, lastName, mobileNumber, birthdate) VALUES (?,?,?,?,?,?,?);");

        $hashPsW = password_hash($password, PASSWORD_BCRYPT);

        if (!$stmt->execute(array($username, $email, $hashPsW, $firstname, $lastname, $mobileNum, $birthdate))) { 
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /?error=try+again"); //stmtfailed
            exit();
        }

        $stmt = null;
    }

    protected function checkDriver($username, $email) {
        $stmt =$this->connect()->prepare('SELECT driverid FROM driver WHERE username = ? OR email = ?;');
        if (!$stmt->execute(array($username, $email))) {
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /?error=try+again"); //stmtfailed
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