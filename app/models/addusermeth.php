<?php

use core\Flash;

class AddedDrvr extends ConnectDatabase {
    protected function setDriver($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate) {
        $sql = $this->connect()->prepare("INSERT INTO driver (username, email, password, firstName, lastName, mobileNumber, birthdate) VALUES (?,?,?,?,?,?,?);");

        $hashPsW = password_hash($password, PASSWORD_BCRYPT);

        if (!$stmt->execute(array($username, $email, $hashPsW, $firstname, $lastname, $mobileNum, $birthdate))) {
            $alert = new Flash();
            $stmt = null;
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /?error=try+again"); //stmtfailed
            exit();
        }

        $stmt = null;
    }

    protected function addToReset($email) {
        $alert = new Flash();
        $sql = "INSERT INTO pwdreset (drvr_email)
                VALUES (:email)";
        $stmt = $this->connect()->prepare($sql);
        $result = $stmt->execute([
            ':email' => $email
        ]);

        if (!$result) {
            $alert::setMsg('error', 'There was a problem. Try again.');
            header("Location: /?error=try+again");
            exit();
        }
    }

    /*protected function checkIfEmailExist($email) {
        $alert = new Flash();
        $sql = "SELECT drvr_email FROM pwdreset 
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
    }*/

    protected function checkDriver($username, $email) {
        $stmt =$this->connect()->prepare('SELECT driverid FROM driver WHERE username = ? OR email = ?;');
        if (!$stmt->execute(array($username, $email))) {
            $alert = new Flash();
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