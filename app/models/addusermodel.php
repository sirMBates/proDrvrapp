<?php

use core\Database;
use core\Flash;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";

class AddedDrvr {
    protected function setDriver($username, $email, $password) {
        $db = new Database;
        $alert = new Flash();
        $pdo = $db->connect();
        $sql = "INSERT INTO driver (
                username, email, operator_id, password, first_name, last_name, mobile_number, birth_date, profile_picture) 
                VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $db->connect()->prepare($sql);

        $hashPsW = password_hash($password, PASSWORD_BCRYPT);
        $tmpFirstName = '';
        $tmpLastName = '';
        $tmpMobileNum = '';
        $tmpOperatorId = '';
        $tmpBirthDate = NULL;
        $tmpProfilePicture = NULL;
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $tmpOperatorId);
        $stmt->bindParam(4, $hashPsW);
        $stmt->bindParam(5, $tmpFirstName);
        $stmt->bindParam(6, $tmpLastName);
        $stmt->bindParam(7, $tmpMobileNum);
        $stmt->bindParam(8, $tmpBirthDate);
        $stmt->bindParam(9, $tmpProfilePicture);

        $result = $stmt->execute();

        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again"); //stmtfailed
            exit();
        }

        $drvr_id = $pdo->lastInsertId();
        $_SESSION['driver_id'] = $drvr_id;
        $token = '';
        $tokenExpTime = NULL;
        $sql2 = "INSERT INTO pwd_reset (email, driver_id, reset_token, token_exp_time)
                VALUES (?,?,?,?)";
        $stmt2 = $db->connect()->prepare($sql2);

        $stmt2->bindParam(1, $email);
        $stmt2->bindParam(2, $drvr_id);
        $stmt2->bindParam(3, $token);
        $stmt2->bindParam(4, $tokenExpTime);
        
        $result2 = $stmt2->execute();

        if (!$result2) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again");  //stmtfailed
            exit();
        }
    }

    protected function checkDriver($username, $email) {
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT username, email FROM driver
                WHERE username = :username OR email = :email";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $result = $stmt->execute();

        $stmt->fetch();

        if (!$stmt) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /signup?error=try+again"); //stmtfailed
            exit();
        }

        $dbUsername;
        $dbEmail;
        $resultCheck;

        if ($stmt > 0) {
            $dbUsername = $stmt['username'];
            $dbEmail = $stmt['email'];
            if ($username === $dbUsername || $email === $dbEmail) {
                $resultCheck = true;
            } else {
                $resultCheck = false;
            }
        return $resultCheck;
        }
    }
}

?>