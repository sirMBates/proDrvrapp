<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class RegProInfo {
    protected function addDriverDetails($firstname, $lastname, $mobileNum, $birthdate, $username) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $sql = "UPDATE driver 
                SET firstName = ?, lastName = ?, mobileNumber = ?, birthdate = ? 
                WHERE username = ?";
        $stmt = $db->connect()->prepare($sql);

        $encryptedFirstName = Crypto::encrypt($firstname, $key);
        $encryptedLastName = Crypto::encrypt($lastname, $key);
        $encryptedMobileNum = Crypto::encrypt($mobileNum, $key);
        $encryptedBirthdate = Crypto::encrypt($birthdate, $key);
        $stmt->bindParam(1, $encryptedFirstName);
        $stmt->bindParam(2, $encryptedLastName);
        $stmt->bindParam(3, $encryptedMobileNum);
        $stmt->bindParam(4, $encryptedBirthdate);
        $stmt->bindParam(5, $username);

        $result = $stmt->execute();
        if (!$result) {
            $alert::setMsg('error', 'An unexpected error occurred. Please try again.');
            header("Location: /register?error=try+again"); //stmtfailed
            exit();
        }
        $stmt = null;
    }
}
?>