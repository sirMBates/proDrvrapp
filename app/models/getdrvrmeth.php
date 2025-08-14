<?php

use core\Database;
use core\Flash;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;
require_once "../vendor/autoload.php";
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.local.env');
$dotenv->load();

class GetDriver {
    protected function retrieveDriver($drvrid) {
        $key = Key::loadFromAsciiSafeString($_ENV['SECRET_KEY']);
        $db = new Database;
        $alert = new Flash();
        $sql = "SELECT * FROM driver
                WHERE driverid = :driverid";
        $stmt = $db->connect()->prepare($sql);
        $stmt->bindParam(':driverid', $drvrid);
        $stmt->execute();

        $result = $stmt->fetchAll();

        if (!$stmt || $stmt->rowCount() === 0) {
            $alert::setMsg('error', 'There seems to be a problem. Try again later.');
            header("location: /?error=try+again");
            exit();
        }

        $encryptedEmail = $result['email'];
        $encryptedFirstName = $result['firstName'];
        $encryptedLastName = $result['lastName'];
        $encryptedMobileNum = $result['mobileNumber'];
        $encryptedBirthdate = $result['birthdate'];
        return {
            $drvrid = $result['driverid'];
            $username = $result['username'];
            $dbEmail = Crypto::decrypt($encryptedEmail, $key);
            $dbFirstName = Crypto::decrypt($encryptedFirstName, $key);
            $dbLastName = Crypto::decrypt($encryptedLastName, $key);
            $dbMobileNum = Crypto::decrypt($encryptedMobileNum, $key);
            $dbBirthdate = Crypto::decrypt($encryptedBirthdate, $key);
        }
    }

    public function getDrvrStats($drvrid) {
        return $this->retrieveDriver($drvrid);
    }
}

?>