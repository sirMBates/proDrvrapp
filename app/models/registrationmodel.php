<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once base_path("vendor/autoload.php");
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class RegistrationInformation {
    protected function addDriverDetails($newCompanyId, $firstname, $lastname, $mobileNum, $birthdate, $username) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $sql = "UPDATE driver 
                SET operator_id = ?, first_name = ?, last_name = ?, mobile_number = ?, birth_date = ? 
                WHERE username = ?";
        $stmt = $db->connect()->prepare($sql);

        $encryptedFirstName = Crypto::encrypt($firstname, $key);
        $encryptedLastName = Crypto::encrypt($lastname, $key);
        $encryptedMobileNum = Crypto::encrypt($mobileNum, $key);
        $encryptedBirthdate = Crypto::encrypt($birthdate, $key);
        $stmt->bindParam(1, $newCompanyId);
        $stmt->bindParam(2, $encryptedFirstName);
        $stmt->bindParam(3, $encryptedLastName);
        $stmt->bindParam(4, $encryptedMobileNum);
        $stmt->bindParam(5, $encryptedBirthdate);
        $stmt->bindParam(6, $username);

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