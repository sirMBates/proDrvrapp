<?php

use core\Flash;

class AddedDrvr extends ConnectDatabase {
    protected function setDriver($username, $email, $password, $firstname, $lastname, $mobileNum, $birthdate) {
        $alert = new Flash();
        $sql = "INSERT INTO driver (
                username, email, password, firstName, lastName, mobileNumber, birthdate) 
                VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->connect()->prepare($sql);

        $hashPsW = password_hash($password, PASSWORD_BCRYPT);

        if (!$stmt->execute(array($username, $email, $hashPsW, $firstname, $lastname, $mobileNum, $birthdate))) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /?error=try+again"); //stmtfailed
            exit();
        }

        $drvrIdMainTab = $this->connect()->lastInsertId();
        $sql2 = "INSERT INTO pwdreset (resetid, drvr_email)
                VALUES (:resetid, :email)";
        $stmt2 = $this->connect()->prepare($sql2);
        $result = $stmt2->execute([
            ':resetid' => $drvrIdMainTab,
            ':email' => $email
        ]);

        if (!$result) {
            $alert::setMsg('error', 'There was a problem. Try again.');
            header("Location: /error?=try+again");  //stmtfailed
            exit();
        }
    }

    /*protected function addToReset($email) {
        $alert = new Flash();
        $sql = "INSERT INTO pwdreset (resetid, drvr_email)
                VALUES (:resetid, :email)";
        $stmt = $this->connect()->prepare($sql);
        $result = $stmt->execute([
            ':resetid' => $drvrIdMainTab,
            ':email' => $email
        ]);

        if (!$result) {
            $alert::setMsg('error', 'There was a problem. Try again.');
            header("Location: /?error=try+again");
            exit();
        }
    }*/

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