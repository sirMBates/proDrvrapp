<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class AddedDrvr {
    protected function setDriver($username, $email, $password) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $sql = "INSERT INTO driver (
                username, email, password, firstName, lastName, mobileNumber, birthdate) 
                VALUES (?,?,?,?,?,?,?)";
        $stmt = $db->connect()->prepare($sql);

        $encryptedEmail = Crypto::encrypt($email, $key);
        $hashPsW = password_hash($password, PASSWORD_BCRYPT);
        $tmpFirstName = '';
        $tmpLastName = '';
        $tmpMobileNum = '';
        $tmpBirthDate = NULL;
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $encryptedEmail);
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
        $stmt2 = $db->connect()->prepare($sql2);
        $stmt2->bindParam(1, $encryptedEmail);
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
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
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
            $encryptedEmail = $stmt['email'];
            $dbEmail = Crypto::decrypt($encryptedEmail, $key);
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